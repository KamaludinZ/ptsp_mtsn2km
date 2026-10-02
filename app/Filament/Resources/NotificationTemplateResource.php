<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotificationTemplateResource\Pages;
use App\Models\NotificationSetting;
use App\Models\NotificationTemplate;
use App\Support\NotificationTemplates;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

/**
 * Template notifikasi: the wording of every notification per channel and
 * whether it is sent at all. Templates are fixed by event; they are edited,
 * not created or deleted.
 */
class NotificationTemplateResource extends Resource
{
    protected static ?string $model = NotificationTemplate::class;

    protected static ?string $slug = 'template-notifikasi';

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Manajemen Sistem';

    protected static ?string $navigationLabel = 'Template Notifikasi';

    protected static ?string $modelLabel = 'template notifikasi';

    protected static ?string $pluralModelLabel = 'Template Notifikasi';

    public static function can(string $action, ?Model $record = null): bool
    {
        return in_array($action, ['viewAny', 'view', 'update'], true) && (bool) auth()->user()?->hasRole('admin');
    }

    /** Sample values for the live preview. */
    public const SAMPLE = [
        'nama' => 'Budi Santoso',
        'nomor_tiket' => 'TKT-20261002-0007',
        'layanan' => 'Legalisir Ijazah',
        'status' => 'Diproses',
        'catatan' => 'Berkas sudah lengkap.',
        'tautan' => 'https://ptsp.example/tracking',
        'instansi' => 'MTsN 2 Kota Malang',
    ];

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(fn (?NotificationTemplate $record) => ($record ? NotificationTemplates::EVENTS[$record->key] ?? $record->key : 'Template')
                . ' · ' . ($record ? NotificationSetting::CHANNELS[$record->channel] ?? $record->channel : ''))
                ->schema([
                    Forms\Components\Toggle::make('is_active')->label('Kirim notifikasi ini')
                        ->helperText('Matikan bila notifikasi ini tidak perlu dikirim lewat kanal ini.'),
                    Forms\Components\TextInput::make('subject')->label('Subjek email')->maxLength(255)
                        ->required(fn (?NotificationTemplate $record) => $record?->channel === 'email')
                        ->visible(fn (?NotificationTemplate $record) => $record?->channel === 'email')
                        ->live(debounce: 500),
                    Forms\Components\Textarea::make('body')->label('Isi pesan')->required()->rows(8)->maxLength(4000)
                        ->helperText('Tulis {placeholder} untuk data yang diisi otomatis. Untuk WhatsApp, *tebal* dan _miring_ didukung.')
                        ->live(debounce: 500),
                ]),
            Forms\Components\Grid::make(['default' => 1, 'lg' => 2])->schema([
                Forms\Components\Section::make('Placeholder tersedia')
                    ->icon('heroicon-o-code-bracket')
                    ->schema([
                        Forms\Components\Placeholder::make('placeholders')->hiddenLabel()
                            ->helperText('Klik untuk menyisipkan ke isi pesan pada posisi kursor.')
                            ->content(new HtmlString(collect(NotificationTemplates::PLACEHOLDERS)
                                ->map(fn (string $label, string $key) => sprintf(
                                    '<p style="margin:0 0 .25rem"><button type="button" class="font-mono text-primary-600 dark:text-primary-400" x-on:click="%s">{%s}</button> — %s</p>',
                                    e(self::insertScript('{' . $key . '}')),
                                    e($key),
                                    e($label),
                                ))
                                ->implode(''))),
                    ]),
                Forms\Components\Section::make('Pratinjau')
                    ->icon('heroicon-o-eye')
                    ->description('Dengan contoh data.')
                    ->schema([
                        Forms\Components\Placeholder::make('preview')->hiddenLabel()
                            ->content(fn (Get $get) => new HtmlString(
                                (filled($get('subject')) ? '<p style="margin:0 0 .5rem;font-weight:600">' . e(NotificationTemplates::render($get('subject'), self::SAMPLE)) . '</p>' : '')
                                . '<div style="white-space:pre-line">' . e(NotificationTemplates::render((string) $get('body'), self::SAMPLE)) . '</div>'
                            )),
                    ]),
            ]),
        ])->columns(1);
    }

    /** Alpine handler: insert $text into the body textarea at the cursor. */
    private static function insertScript(string $text): string
    {
        return "const el = document.getElementById('data.body'); if (! el) return;"
            . ' const at = el.selectionStart ?? el.value.length;'
            . ' el.value = el.value.slice(0, at) + ' . json_encode($text) . ' + el.value.slice(el.selectionEnd ?? at);'
            . " el.dispatchEvent(new Event('input')); el.focus();"
            . ' el.selectionStart = el.selectionEnd = at + ' . mb_strlen($text) . ';';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')->label('Notifikasi')->weight('semibold')
                    ->formatStateUsing(fn (string $state) => NotificationTemplates::EVENTS[$state] ?? $state)
                    ->description(fn (NotificationTemplate $record) => $record->subject),
                Tables\Columns\TextColumn::make('channel')->label('Kanal')->badge()
                    ->formatStateUsing(fn (string $state) => NotificationSetting::CHANNELS[$state] ?? $state)
                    ->color(fn (string $state) => $state === 'whatsapp' ? 'success' : 'info')
                    ->description(fn (NotificationTemplate $record) => NotificationSetting::for($record->channel)->is_enabled ? null : 'Kanal nonaktif'),
                Tables\Columns\TextColumn::make('body')->label('Isi pesan')->searchable(['body', 'subject'])->limit(90)->wrap()->color('gray'),
                Tables\Columns\ToggleColumn::make('is_active')->label('Kirim')
                    ->updateStateUsing(function (NotificationTemplate $record, bool $state) {
                        $record->update(['is_active' => $state, 'updated_by' => auth()->id()]);

                        return $state;
                    }),
                Tables\Columns\TextColumn::make('updated_at')->label('Diubah')->since()->toggleable(),
            ])
            ->defaultSort('key')
            ->searchPlaceholder('Cari isi atau subjek pesan')
            ->filters([
                Tables\Filters\SelectFilter::make('channel')->label('Kanal')->options(NotificationSetting::CHANNELS),
                Tables\Filters\SelectFilter::make('key')->label('Notifikasi')->options(NotificationTemplates::EVENTS),
                Tables\Filters\TernaryFilter::make('is_active')->label('Status')
                    ->trueLabel('Dikirim')->falseLabel('Tidak dikirim'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Ubah'),
            ])
            ->emptyStateHeading('Belum ada template notifikasi');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNotificationTemplates::route('/'),
            'edit' => Pages\EditNotificationTemplate::route('/{record}/edit'),
        ];
    }
}
