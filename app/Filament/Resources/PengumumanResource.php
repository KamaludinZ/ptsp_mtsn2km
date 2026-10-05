<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PengumumanResource\Pages;
use App\Models\Pengumuman;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PengumumanResource extends Resource
{
    use \App\Filament\Concerns\AdminOnly;

    protected static ?string $model = Pengumuman::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationLabel = 'Pengumuman';

    protected static ?string $navigationGroup = 'Manajemen Pengumuman';

    protected static ?string $pluralModelLabel = 'Pengumuman';

    public static function form(Form $form): Form
    {
        return $form
            ->columns(['default' => 1, 'xl' => 5])
            ->schema([
                Forms\Components\Group::make()->columnSpan(['xl' => 3])->schema([
                    Forms\Components\Section::make('Isi pengumuman')
                        ->schema([
                            Forms\Components\TextInput::make('title')
                                ->label('Judul')
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true),
                            Forms\Components\TextInput::make('category')
                                ->label('Kategori')
                                ->datalist(fn () => Pengumuman::categories())
                                ->placeholder('mis. akademik, umum, kegiatan')
                                ->maxLength(50)
                                ->dehydrateStateUsing(fn (?string $state) => filled($state) ? \Illuminate\Support\Str::lower(trim($state)) : null)
                                ->live(onBlur: true),
                            Forms\Components\RichEditor::make('content')
                                ->label('Isi')
                                ->required()
                                ->disableToolbarButtons(['attachFiles'])
                                ->live(debounce: 1000),
                            Forms\Components\TextInput::make('author')
                                ->label('Penulis')
                                ->default(fn () => auth()->user()?->name)
                                ->maxLength(255),
                        ]),
                    Forms\Components\Section::make('Jadwal tayang')
                        ->description('Pengumuman tampil di situs dari tanggal tayang sampai tanggal berakhir (kosongkan bila tanpa batas).')
                        ->columns(['default' => 1, 'sm' => 3])
                        ->schema([
                            Forms\Components\DatePicker::make('publish_date')
                                ->label('Tanggal tayang')
                                ->default(today())
                                ->native(false)
                                ->displayFormat('d M Y')
                                ->required()
                                ->live(),
                            Forms\Components\DatePicker::make('end_date')
                                ->label('Tanggal berakhir')
                                ->native(false)
                                ->displayFormat('d M Y')
                                ->afterOrEqual('publish_date')
                                ->validationMessages(['after_or_equal' => 'Tanggal berakhir tidak boleh sebelum tanggal tayang.'])
                                ->live(),
                            Forms\Components\Toggle::make('is_active')
                                ->label('Tayangkan')
                                ->helperText('Matikan untuk menyimpan sebagai draf.')
                                ->default(true)
                                ->inline(false)
                                ->live(),
                        ]),
                    Forms\Components\Section::make('Lampiran & tautan')
                        ->collapsible()
                        ->columns(['default' => 1, 'sm' => 2])
                        ->schema([
                            Forms\Components\FileUpload::make('attachment')
                                ->label('Berkas lampiran')
                                ->directory('pengumuman-attachments')
                                ->preserveFilenames()
                                ->maxSize(10240)
                                ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/jpeg', 'image/png'])
                                ->helperText('PDF, Word, atau gambar; maksimal 10 MB.'),
                            Forms\Components\TextInput::make('url')
                                ->label('Tautan luar')
                                ->url()
                                ->placeholder('https://…')
                                ->maxLength(255)
                                ->live(onBlur: true),
                        ]),
                ]),
                Forms\Components\Section::make('Pratinjau')
                    ->description('Seperti yang dilihat pengunjung situs.')
                    ->icon('heroicon-o-eye')
                    ->columnSpan(['xl' => 2])
                    ->schema([
                        Forms\Components\ViewField::make('preview')
                            ->hiddenLabel()
                            ->dehydrated(false)
                            ->view('filament.forms.pengumuman-preview'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('publish_date', 'desc')
            ->searchPlaceholder('Cari judul atau isi pengumuman…')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->weight('semibold')
                    ->wrap()
                    ->searchable(query: fn (Builder $query, string $search) => $query->where(fn (Builder $q) => $q
                        ->where('title', 'ilike', "%{$search}%")->orWhere('content', 'ilike', "%{$search}%")))
                    ->sortable()
                    ->description(fn (Pengumuman $record) => $record->category ? \Illuminate\Support\Str::headline($record->category) : null),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->state(fn (Pengumuman $record) => \App\Support\ContentStatus::of($record))
                    ->formatStateUsing(fn (string $state) => \App\Support\ContentStatus::LABELS[$state])
                    ->color(fn (string $state) => \App\Support\ContentStatus::COLORS[$state]),
                Tables\Columns\TextColumn::make('publish_date')
                    ->label('Tayang')
                    ->date('d M Y')
                    ->sortable()
                    ->description(fn (Pengumuman $record) => $record->end_date ? 's.d. ' . $record->end_date->translatedFormat('d M Y') : 'tanpa batas'),
                Tables\Columns\TextColumn::make('view_count')
                    ->label('Dilihat')
                    ->numeric()
                    ->alignEnd()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('author')
                    ->label('Penulis')
                    ->placeholder('–')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diubah')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(\App\Support\ContentStatus::LABELS)
                    ->query(fn (Builder $query, array $data) => filled($data['value'] ?? null) ? \App\Support\ContentStatus::scope($query, $data['value']) : $query),
                Tables\Filters\SelectFilter::make('category')
                    ->label('Kategori')
                    ->options(fn () => collect(Pengumuman::categories())->mapWithKeys(fn (string $category) => [$category => \Illuminate\Support\Str::headline($category)])->all()),
                Tables\Filters\Filter::make('publish_date')
                    ->label('Tanggal tayang')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Tayang dari'),
                        Forms\Components\DatePicker::make('until')->label('Tayang sampai'),
                    ])
                    ->columns(2)
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('publish_date', '>=', $date))
                        ->when($data['until'] ?? null, fn (Builder $q, $date) => $q->whereDate('publish_date', '<=', $date)))
                    ->indicateUsing(fn (array $data) => collect([
                        ($data['from'] ?? null) ? 'Tayang dari ' . \Illuminate\Support\Carbon::parse($data['from'])->translatedFormat('j M Y') : null,
                        ($data['until'] ?? null) ? 'sampai ' . \Illuminate\Support\Carbon::parse($data['until'])->translatedFormat('j M Y') : null,
                    ])->filter()->join(' ') ?: null),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\Action::make('publish')
                        ->label('Tayangkan sekarang')
                        ->icon('heroicon-m-signal')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalDescription('Pengumuman langsung tampil di situs mulai hari ini.')
                        ->visible(fn (Pengumuman $record) => \App\Support\ContentStatus::of($record) !== 'tayang')
                        ->action(function (Pengumuman $record, Tables\Actions\Action $action) {
                            \App\Support\ContentStatus::publish($record);
                            $action->success();
                        })
                        ->successNotificationTitle('Pengumuman ditayangkan'),
                    Tables\Actions\Action::make('draft')
                        ->label('Jadikan draf')
                        ->icon('heroicon-m-eye-slash')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->modalDescription('Pengumuman disembunyikan dari situs; isinya tetap tersimpan.')
                        ->visible(fn (Pengumuman $record) => $record->is_active)
                        ->action(function (Pengumuman $record, Tables\Actions\Action $action) {
                            $record->update(['is_active' => false]);
                            $action->success();
                        })
                        ->successNotificationTitle('Pengumuman dijadikan draf'),
                    Tables\Actions\Action::make('end')
                        ->label('Akhiri sekarang')
                        ->icon('heroicon-m-stop-circle')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalDescription('Tanggal berakhir diisi kemarin, sehingga pengumuman tidak lagi tampil (tetap tersimpan sebagai arsip).')
                        ->visible(fn (Pengumuman $record) => \App\Support\ContentStatus::of($record) === 'tayang')
                        ->action(function (Pengumuman $record, Tables\Actions\Action $action) {
                            \App\Support\ContentStatus::end($record);
                            $action->success();
                        })
                        ->successNotificationTitle('Pengumuman diakhiri'),
                    Tables\Actions\DeleteAction::make()
                        ->modalDescription('Pengumuman dan berkas lampirannya dihapus permanen.'),
                ])->label('Aksi')->icon('heroicon-m-ellipsis-vertical'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('publish')->label('Tayangkan')->icon('heroicon-m-signal')->color('success')
                        ->requiresConfirmation()
                        ->action(fn (\Illuminate\Support\Collection $records) => $records->each(fn (Pengumuman $r) => \App\Support\ContentStatus::publish($r)))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('draft')->label('Jadikan draf')->icon('heroicon-m-eye-slash')
                        ->requiresConfirmation()
                        ->action(fn (\Illuminate\Support\Collection $records) => $records->each->update(['is_active' => false]))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateIcon('heroicon-o-megaphone')
            ->emptyStateHeading('Tidak ada pengumuman')
            ->emptyStateDescription('Tidak ada pengumuman pada tab, pencarian, atau saringan ini.');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPengumumen::route('/'),
            'create' => Pages\CreatePengumuman::route('/create'),
            'view' => Pages\ViewPengumuman::route('/{record}'),
            'edit' => Pages\EditPengumuman::route('/{record}/edit'),
        ];
    }
}