<?php

namespace App\Filament\Pages\Reports;

use App\Exceptions\TicketActionException;
use App\Models\Survey;
use App\Models\SurveyEdition;
use App\Models\SurveyResponse;
use App\Models\Ticket;
use App\Support\RoleAccess;
use App\Support\SurveyReminders;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Url;

/**
 * Responden survei SKM/SPAK: who rated which request, and the completed
 * requests still waiting for a rating, with a reminder to their applicant.
 */
class SurveyRespondents extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Pengawasan';

    protected static ?string $navigationLabel = 'Responden Survei';

    protected static ?string $title = 'Responden Survei';

    protected static ?string $slug = 'responden-survei';

    protected static ?int $navigationSort = 4;

    protected static string $view = 'filament.pages.survey-respondents';

    #[Url]
    public string $tab = 'responden';

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->hasAnyRole(RoleAccess::COMPLAINT_HANDLERS);
    }

    public function getSubheading(): ?string
    {
        return $this->tab === 'belum'
            ? 'Permohonan selesai yang belum dinilai pemohonnya. Pengingat dikirim lewat kanal notifikasi aktif, paling sering sekali sehari per permohonan.'
            : 'Setiap pengisian survei kepuasan beserta permohonan yang dinilai.';
    }

    public function updatedTab(): void
    {
        $this->tab = in_array($this->tab, ['responden', 'belum'], true) ? $this->tab : 'responden';
        $this->resetTable();
    }

    public function unratedCount(): int
    {
        return SurveyReminders::unrated()->count();
    }

    public function table(Table $table): Table
    {
        return $this->tab === 'belum' ? $this->unratedTable($table) : $this->respondentsTable($table);
    }

    private function respondentsTable(Table $table): Table
    {
        return $table
            ->query(SurveyResponse::query()->whereNotNull('completed_at')->with(['survey:id,name,type', 'edition:id,name', 'ticket:id,ticket_number,service_id', 'ticket.service:id,name', 'user:id,name']))
            ->defaultSort('completed_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('completed_at')->label('Diisi')->dateTime('d M Y H:i')->sortable(),
                Tables\Columns\TextColumn::make('survey.type')->label('Survei')->badge()
                    ->formatStateUsing(fn (?string $state) => strtoupper((string) $state)),
                Tables\Columns\TextColumn::make('edition.name')->label('Edisi')->placeholder('–')->toggleable(),
                Tables\Columns\TextColumn::make('responden')->label('Responden')
                    ->state(fn (SurveyResponse $record) => $record->user?->name ?? $record->respondent_email ?? 'Anonim'),
                Tables\Columns\TextColumn::make('ticket.ticket_number')->label('Tiket')->placeholder('–')->searchable()
                    ->description(fn (SurveyResponse $record) => $record->ticket?->service?->name),
                Tables\Columns\TextColumn::make('comments')->label('Saran')->limit(60)->wrap()->placeholder('–')->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('survey_type')->label('Survei')
                    ->options(fn () => Survey::query()->distinct()->pluck('type', 'type')->mapWithKeys(fn ($t) => [$t => strtoupper($t)])->all())
                    ->query(fn (Builder $query, array $data) => $query->when($data['value'] ?? null, fn (Builder $q, $type) => $q->whereHas('survey', fn (Builder $s) => $s->where('type', $type)))),
                Tables\Filters\SelectFilter::make('survey_edition_id')->label('Edisi')
                    ->options(fn () => SurveyEdition::query()->orderByDesc('id')->pluck('name', 'id')->all()),
                Tables\Filters\Filter::make('tanggal')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Dari tanggal'),
                        Forms\Components\DatePicker::make('until')->label('Sampai tanggal'),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['from'], fn (Builder $q, $date) => $q->whereDate('completed_at', '>=', $date))
                        ->when($data['until'], fn (Builder $q, $date) => $q->whereDate('completed_at', '<=', $date)))
                    ->indicateUsing(fn (array $data) => array_filter([
                        $data['from'] ? 'Dari ' . Carbon::parse($data['from'])->translatedFormat('j M Y') : null,
                        $data['until'] ? 'Sampai ' . Carbon::parse($data['until'])->translatedFormat('j M Y') : null,
                    ])),
            ])
            ->emptyStateIcon('heroicon-o-users')
            ->emptyStateHeading('Belum ada responden')
            ->emptyStateDescription('Pengisian survei kepuasan akan tampil di sini.');
    }

    private function unratedTable(Table $table): Table
    {
        return $table
            ->query(SurveyReminders::unrated()->with(['service:id,name', 'user:id,name,email,whatsapp_number']))
            ->defaultSort('actual_completion_date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('ticket_number')->label('Tiket')->weight('semibold')->searchable(),
                Tables\Columns\TextColumn::make('service.name')->label('Layanan')->wrap(),
                Tables\Columns\TextColumn::make('user.name')->label('Pemohon')->searchable()
                    ->description(fn (Ticket $record) => collect([$record->user?->email, $record->user?->whatsapp_number])->filter()->join(' · ') ?: 'Tanpa kontak'),
                Tables\Columns\TextColumn::make('actual_completion_date')->label('Selesai')->since()->sortable(),
                Tables\Columns\TextColumn::make('pengingat')->label('Pengingat terakhir')
                    ->state(fn (Ticket $record) => ($at = SurveyReminders::lastSentAt($record)) ? Carbon::parse($at)->diffForHumans() : null)
                    ->placeholder('Belum dikirim'),
            ])
            ->actions([
                Tables\Actions\Action::make('remind')
                    ->label('Kirim pengingat')
                    ->icon('heroicon-m-bell-alert')
                    ->requiresConfirmation()
                    ->modalDescription(fn (Ticket $record) => 'Pemohon ' . $record->user?->name . ' diminta menilai layanan ' . $record->service?->name . ' lewat kanal notifikasi aktif.')
                    ->action(fn (Ticket $record) => $this->remind(new Collection([$record]))),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('remindSelected')
                    ->label('Kirim pengingat')
                    ->icon('heroicon-m-bell-alert')
                    ->requiresConfirmation()
                    ->action(fn (Collection $records) => $this->remind($records))
                    ->deselectRecordsAfterCompletion(),
            ])
            ->emptyStateIcon('heroicon-o-check-badge')
            ->emptyStateHeading('Semua permohonan selesai sudah dinilai')
            ->emptyStateDescription('Tidak ada pemohon yang perlu diingatkan.');
    }

    /** @param  Collection<int, Ticket>  $tickets */
    private function remind(Collection $tickets): void
    {
        $sent = 0;
        $problems = [];

        foreach ($tickets as $ticket) {
            try {
                SurveyReminders::remind($ticket, auth()->user()) ? $sent++ : $problems[] = "{$ticket->ticket_number}: tidak ada kanal aktif atau kontak pemohon.";
            } catch (TicketActionException $e) {
                $problems[] = $e->getMessage();
            }
        }

        Notification::make()
            ->title($sent ? "{$sent} pengingat survei dikirim" : 'Tidak ada pengingat yang dikirim')
            ->body($problems ? implode('<br>', array_map('e', $problems)) : null)
            ->{$sent ? 'success' : 'warning'}()
            ->send();
    }
}
