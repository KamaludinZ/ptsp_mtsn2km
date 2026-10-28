<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VisitorResource\Pages;
use App\Models\Visitor;
use App\Models\VisitorMaster;
use Filament\Forms;
use Filament\Forms\Form;
use App\Exceptions\TicketActionException;
use App\Models\Service;
use App\Models\User;
use App\Services\FrontDeskService;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class VisitorResource extends Resource
{
    protected static ?string $model = Visitor::class;

    protected static ?string $navigationLabel = 'Buku Tamu';

    protected static ?string $navigationGroup = 'Loket';

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $modelLabel = 'tamu';

    protected static ?string $pluralModelLabel = 'Buku Tamu';

    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        $count = Visitor::whereDate('check_in_time', today())->whereNull('check_out_time')->count();

        return $count ? (string) $count : null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Tamu yang masih di lokasi';
    }

    public static function form(Form $form): Form
    {
        $creating = $form->getOperation() === 'create';

        $visit = fn (Forms\Get $get) => ! $creating || $get('guest_type') === 'kunjungan';
        $applicant = fn (Forms\Get $get) => $creating && $get('guest_type') === 'pemohon';
        // As on the public form, an applicant names an institution only when coming for one.
        $institution = fn (Forms\Get $get) => ! $applicant($get) || $get('applicant_type') === 'instansi_perusahaan';

        return $form
            ->schema([
                // As the public visitor book: a visiting guest, or someone come for a PTSP service.
                Forms\Components\Section::make('Jenis tamu')
                    ->schema([
                        Forms\Components\ToggleButtons::make('guest_type')
                            ->hiddenLabel()
                            ->options(Visitor::GUEST_TYPES)
                            ->icons(['kunjungan' => 'heroicon-o-user-group', 'pemohon' => 'heroicon-o-document-text'])
                            // Opened from Buku Tamu's "Pemohon layanan offline" button: ?jenis=pemohon.
                            ->default(fn () => array_key_exists($type = (string) request()->query('jenis'), Visitor::GUEST_TYPES) ? $type : 'kunjungan')
                            ->inline()
                            ->required()
                            ->live()
                            ->helperText(fn (Forms\Get $get) => $get('guest_type') === 'pemohon'
                                ? 'Datang untuk mengajukan atau menanyakan layanan PTSP secara langsung di loket.'
                                : 'Datang untuk bertemu guru, pegawai, atau unit madrasah.'),
                    ])
                    ->visible($creating),

                Forms\Components\Section::make('Identitas tamu')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama lengkap')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->label('Nomor telepon / WhatsApp')
                            ->tel()
                            // Required as on the public form: it is how the guest checks out there.
                            ->required($creating)
                            ->maxLength(20),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\Select::make('applicant_type')
                            ->label('Status pemohon')
                            ->options(Visitor::APPLICANT_STATUSES)
                            ->in(array_keys(Visitor::APPLICANT_STATUSES))
                            ->placeholder('Pilih status')
                            ->live()
                            ->visible($applicant),
                        Forms\Components\Select::make('institution_category')
                            ->label('Jenis instansi / perusahaan')
                            ->visible($institution)
                            ->options(Visitor::INSTITUTION_CATEGORIES)
                            // Older guests may hold another category: checked when registering only.
                            ->in(Visitor::institutionCategoryKeys(), $creating)
                            ->placeholder('Pilih kategori'),
                        Forms\Components\TextInput::make('institution')
                            ->label('Instansi / asal')
                            ->visible($institution)
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Kunjungan')
                    ->visible($visit)
                    ->schema([
                        // Registering: the public form's "Tujuan kunjungan" (master list, or Lainnya + text).
                        Forms\Components\Select::make('purpose')
                            ->label('Tujuan kunjungan')
                            ->options(fn () => array_combine($purposes = VisitorMaster::options('tujuan'), $purposes))
                            ->in(fn () => VisitorMaster::options('tujuan'))
                            ->required()
                            ->live()
                            ->helperText(fn (?string $state) => $state === 'Layanan PTSP'
                                ? 'Datang untuk mengajukan atau menanyakan layanan? Untuk itu pilih jenis Pemohon Layanan Offline di atas.'
                                : null)
                            ->visible($creating),
                        Forms\Components\TextInput::make('purpose_other')
                            ->label('Tuliskan tujuan kunjungan')
                            ->required()
                            ->maxLength(500)
                            ->visible(fn (Forms\Get $get) => $creating && $get('purpose') === VisitorMaster::OTHER),
                        // Correcting a guest afterwards: the purpose as it was saved.
                        Forms\Components\Textarea::make('purpose')
                            ->label('Keperluan')
                            ->required()
                            ->maxLength(500)
                            ->rows(3)
                            ->columnSpanFull()
                            ->hidden($creating),
                        Forms\Components\Select::make('person_to_meet_id')
                            ->label('Bertemu dengan')
                            ->helperText('Bila tamu menemui orang tertentu.')
                            ->options(fn () => User::query()
                                ->where('is_active', true)
                                ->whereIn('user_type', ['guru', 'pegawai'])
                                ->orderBy('name')
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->visible($creating),
                        Forms\Components\TextInput::make('person_to_meet')
                            ->label('Bertemu dengan')
                            ->maxLength(255)
                            ->hidden($creating),
                        // Faces are personal data: kept on the private disk,
                        // shown only to the front desk (route visitors.photo).
                        Forms\Components\FileUpload::make('photo_path')
                            ->label('Foto tamu')
                            ->image()
                            ->disk('local')
                            ->visibility('private')
                            ->directory('visitor-photos')
                            ->maxSize(2048)
                            ->visible($creating),
                        Forms\Components\Placeholder::make('photo')
                            ->label('Foto tamu')
                            ->content(fn (?Visitor $record) => new \Illuminate\Support\HtmlString(
                                '<img src="' . e(route('visitors.photo', $record)) . '" alt="Foto tamu" style="width:8rem;height:8rem;object-fit:cover;border-radius:.5rem">'
                            ))
                            ->visible(fn (?Visitor $record) => ! $creating && filled($record?->photo_path)),
                        Forms\Components\DateTimePicker::make('check_in_time')
                            ->label('Waktu check-in')
                            ->required()
                            ->hidden($creating),
                        Forms\Components\Placeholder::make('checkout_by')
                            ->label('Cara check-out')
                            ->content(fn (?Visitor $record) => ($checkout = $record?->checkouts()->with('officer:id,name')->first())
                                ? \App\Models\VisitorCheckout::METHODS[$checkout->method] . ($checkout->officer ? " ({$checkout->officer->name})" : '') . ' · ' . $checkout->checked_out_at->translatedFormat('j M Y H:i')
                                : null)
                            ->visible(fn (?Visitor $record) => ! $creating && $record?->checkouts()->exists()),
                        Forms\Components\DateTimePicker::make('check_out_time')
                            ->label('Waktu check-out')
                            ->afterOrEqual('check_in_time')
                            ->validationMessages(['after_or_equal' => 'Waktu check-out tidak boleh sebelum waktu check-in.'])
                            ->hidden($creating),
                        Forms\Components\Textarea::make('notes')
                            ->label($creating ? 'Keperluan lainnya' : 'Catatan')
                            ->placeholder($creating ? 'Jelaskan secara singkat keperluan tamu' : null)
                            ->maxLength($creating ? 1000 : 500)
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('is_obscured')
                            ->label('Samarkan nama di daftar tamu publik')
                            ->helperText('Petugas tetap melihat nama lengkap.')
                            ->default(true)
                            ->visible($creating)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Permohonan layanan')
                    ->description('Layanan yang dituju pemohon yang datang langsung ke loket. Untuk menerbitkan tiket permohonan, daftarkan di Registrasi Layanan.')
                    ->visible($applicant)
                    ->schema([
                        Forms\Components\Select::make('target_service')
                            ->label('Layanan yang dituju')
                            ->options(fn () => Service::where('is_active', true)->orderBy('name')->pluck('name', 'name')->put(VisitorMaster::OTHER, VisitorMaster::OTHER))
                            ->in(fn () => [...Service::where('is_active', true)->pluck('name'), VisitorMaster::OTHER])
                            ->searchable()
                            ->required()
                            ->live()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('target_service_other')
                            ->label('Tuliskan layanan yang dituju')
                            ->required()
                            ->maxLength(500)
                            ->visible(fn (Forms\Get $get) => $get('target_service') === VisitorMaster::OTHER)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan tambahan')
                            ->placeholder('Jelaskan secara singkat keperluan pemohon')
                            ->maxLength(1000)
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('is_obscured')
                            ->label('Samarkan nama di daftar tamu publik')
                            ->helperText('Petugas tetap melihat nama lengkap.')
                            ->default(true)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(['name', 'phone'])
                    ->sortable()
                    ->description(fn (Visitor $record) => $record->phone),
                Tables\Columns\TextColumn::make('guest_type')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => $state === 'pemohon' ? 'Pemohon' : 'Kunjungan')
                    ->color(fn (?string $state) => $state === 'pemohon' ? 'info' : 'gray')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('institution')
                    ->label('Instansi')
                    ->searchable()
                    ->limit(40)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('purpose')
                    ->label('Keperluan')
                    ->searchable()
                    ->limit(50)
                    ->wrap(),
                Tables\Columns\TextColumn::make('person_to_meet')
                    ->label('Menemui')
                    ->searchable()
                    ->placeholder('–')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('check_in_time')
                    ->label('Masuk')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('check_out_time')
                    ->label('Keluar')
                    ->time('H:i')
                    ->placeholder('Masih di lokasi')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('check_in_time')
                    ->label('Tanggal kunjungan')
                    ->form([Forms\Components\DatePicker::make('date')->label('Tanggal kunjungan')])
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['date'] ?? null,
                        fn (Builder $query, $date): Builder => $query->whereDate('check_in_time', $date)
                    ))
                    ->indicateUsing(fn (array $data) => ($data['date'] ?? null) ? 'Tanggal: ' . \Illuminate\Support\Carbon::parse($data['date'])->translatedFormat('j F Y') : null),
                Tables\Filters\TernaryFilter::make('on_site')
                    ->label('Status kunjungan')
                    ->trueLabel('Masih di lokasi')
                    ->falseLabel('Sudah keluar')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNull('check_out_time'),
                        false: fn (Builder $query) => $query->whereNotNull('check_out_time'),
                    ),
                Tables\Filters\SelectFilter::make('guest_type')
                    ->label('Jenis tamu')
                    ->options(Visitor::GUEST_TYPES),
                Tables\Filters\SelectFilter::make('institution_category')
                    ->label('Kategori instansi')
                    ->options(fn () => Visitor::whereNotNull('institution_category')->distinct()->orderBy('institution_category')->pluck('institution_category', 'institution_category')),
            ])
            ->searchPlaceholder('Cari nama, HP, instansi, keperluan')
            ->actions([
                Tables\Actions\Action::make('checkOut')
                    ->label('Check-out')
                    ->icon('heroicon-m-arrow-right-start-on-rectangle')
                    ->color('warning')
                    ->visible(fn (Visitor $record) => ! $record->check_out_time && auth()->user()->can('checkOut', $record))
                    // A visit from an earlier day was left open: the officer says when the guest left.
                    ->modalDescription(fn (Visitor $record) => $record->check_in_time->isToday() ? null
                        : 'Tamu datang ' . $record->check_in_time->translatedFormat('l, j F Y H:i') . ' dan belum dicatat pulang.')
                    ->form(fn (Visitor $record) => $record->check_in_time->isToday() ? [] : [
                        Forms\Components\DateTimePicker::make('left_at')
                            ->label('Waktu pulang')
                            ->required()
                            ->seconds(false)
                            ->minDate($record->check_in_time->copy()->startOfMinute())
                            ->maxDate(now())
                            ->validationMessages(['after_or_equal' => 'Waktu pulang tidak boleh sebelum tamu datang.', 'before_or_equal' => 'Waktu pulang tidak boleh di masa depan.']),
                    ])
                    ->action(function (Visitor $record, array $data) {
                        try {
                            app(FrontDeskService::class)->checkOut($record, auth()->user(), null, isset($data['left_at']) ? \Illuminate\Support\Carbon::parse($data['left_at']) : null);
                            Notification::make()->title("{$record->name} sudah check-out.")->success()->send();
                        } catch (TicketActionException $e) {
                            Notification::make()->title($e->getMessage())->danger()->send();
                        }
                    }),
                Tables\Actions\Action::make('note')
                    ->label('Catatan')
                    ->icon('heroicon-m-pencil-square')
                    ->color('gray')
                    ->visible(fn (Visitor $record) => auth()->user()->can('note', $record))
                    ->fillForm(fn (Visitor $record) => ['notes' => $record->notes])
                    ->form([Forms\Components\Textarea::make('notes')->label('Catatan kunjungan')->rows(4)->maxLength(1000)])
                    ->action(function (Visitor $record, array $data) {
                        $record->update(['notes' => $data['notes']]);
                        Notification::make()->title('Catatan disimpan.')->success()->send();
                    }),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\Action::make('print')
                        ->label('Cetak kartu tamu')
                        ->icon('heroicon-m-printer')
                        ->url(fn (Visitor $record) => route('visitors.print', $record))
                        ->openUrlInNewTab(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('check_in_time', 'desc')
            ->emptyStateHeading('Belum ada tamu')
            ->emptyStateIcon('heroicon-o-user-group');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVisitors::route('/'),
            'create' => Pages\CreateVisitor::route('/create'),
            'view' => Pages\ViewVisitor::route('/{record}'),
            'edit' => Pages\EditVisitor::route('/{record}/edit'),
        ];
    }
}