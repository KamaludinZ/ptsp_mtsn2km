<?php

namespace App\Filament\Actions;

use App\Filament\Resources\PersuratanMasterResource\Pages\ListPersuratanMasters;
use App\Models\PersuratanMaster;
use App\Support\NomorFormat;
use App\Support\NomorFormatSettings;
use App\Support\SuratKeluarNumber;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Illuminate\Support\HtmlString;

/**
 * Editor format nomor di tab Penomoran Otomatis: format token per jenis
 * surat dan mode bulan. Token tak dikenal ditolak. Disimpan ke baris jenis surat
 * (NomorFormatSettings).
 */
class NumberingFormatAction
{
    public static function make(): Action
    {
        return Action::make('numbering')
            ->label('Atur penomoran')
            ->icon('heroicon-m-hashtag')
            ->visible(fn ($livewire) => ($livewire->activeTab ?? null) === ListPersuratanMasters::NUMBERING_TAB)
            ->modalHeading(fn (PersuratanMaster $record) => 'Penomoran ' . $record->nama)
            ->modalDescription('Susun format dari token, pisahkan dengan garis miring. Nomor urut, singkatan unit kerja, klasifikasi, bulan, dan tahun diisi otomatis saat petugas meminta nomor.')
            ->modalSubmitActionLabel('Simpan format')
            // Back to the default format (the numbering used before formats existed).
            ->extraModalFooterActions(fn (Action $action) => [
                $action->makeModalSubmitAction('resetDefault', arguments: ['reset' => true])
                    ->label('Kembalikan ke format bawaan')
                    ->color('gray'),
            ])
            ->fillForm(fn (PersuratanMaster $record) => [
                'format' => NomorFormatSettings::effectiveFormat($record),
                'mode_bulan' => NomorFormatSettings::for($record)['mode_bulan'],
                'timpa_singkatan' => NomorFormatSettings::for($record)['singkatan'] !== null,
                'singkatan' => NomorFormatSettings::for($record)['singkatan'],
            ])
            ->form([
                TextInput::make('format')
                    ->label('Format nomor')
                    ->required()
                    ->maxLength(120)
                    ->live(debounce: 400)
                    ->extraInputAttributes(['class' => 'font-mono', 'spellcheck' => 'false', 'autocomplete' => 'off'])
                    ->helperText('Contoh: {KS}/{N}/{S}/{k}/{M}/{Y}. Token wajib: {N} (nomor urut).')
                    // Pilihan cepat: the two common formats in one click.
                    ->hintActions(collect(NomorFormat::QUICK_FORMATS)->values()->map(fn (string $format, int $i) => FormAction::make('quick' . ($i + 1))
                        ->label($format)
                        ->icon('heroicon-m-bolt')
                        ->link()
                        ->extraAttributes(['class' => 'font-mono', 'data-quick-format' => $format])
                        ->action(fn (Set $set) => $set('format', $format)))->all())
                    ->rules([
                        fn () => function (string $attribute, $value, \Closure $fail) {
                            if ($unknown = NomorFormat::unknownTokens((string) $value)) {
                                $fail('Token tidak dikenal: ' . collect($unknown)->map(fn ($t) => '{' . $t . '}')->join(', ') . '.');
                            }
                            if (! in_array('N', NomorFormat::tokens((string) $value), true)) {
                                $fail('Format wajib memuat {N} agar setiap nomor berbeda.');
                            }
                        },
                    ]),
                Radio::make('mode_bulan')
                    ->label('Bulan pada nomor ({M})')
                    ->options(['arab' => 'Angka Arab (10)', 'romawi' => 'Angka Romawi (X)'])
                    ->default('arab')
                    ->inline()
                    ->required()
                    ->live(),
                // Singkatan unit kerja ({S}): from Pengaturan Aplikasi, unless this letter type overrides it.
                Section::make('Singkatan unit kerja ({S})')
                    ->compact()
                    ->schema([
                        Placeholder::make('singkatan_bawaan')
                            ->label('Dari Pengaturan Aplikasi')
                            ->content(fn () => new HtmlString('<span data-default-abbreviation class="font-mono">' . e(SuratKeluarNumber::kodeSatker()) . '</span>')),
                        Toggle::make('timpa_singkatan')
                            ->label('Timpa singkatan untuk jenis surat ini')
                            ->live()
                            // On: start from the settings value to edit; off: drop the override.
                            ->afterStateUpdated(fn (bool $state, Get $get, Set $set) => $state
                                ? (blank($get('singkatan')) ? $set('singkatan', SuratKeluarNumber::kodeSatker()) : null)
                                : $set('singkatan', null)),
                        TextInput::make('singkatan')
                            ->label('Singkatan khusus')
                            ->placeholder('mis. TU.MTsN2')
                            ->maxLength(30)
                            ->live(debounce: 400)
                            ->extraInputAttributes(['class' => 'font-mono', 'autocomplete' => 'off'])
                            ->visible(fn (Get $get) => (bool) $get('timpa_singkatan'))
                            ->required(fn (Get $get) => (bool) $get('timpa_singkatan'))
                            ->rules([fn () => function (string $attribute, $value, \Closure $fail) {
                                if (filled($value) && ($problem = NomorFormatSettings::singkatanProblem(trim((string) $value)))) {
                                    $fail($problem);
                                }
                            }]),
                        Placeholder::make('singkatan_berlaku')
                            ->label('Singkatan yang dipakai')
                            ->content(fn (Get $get) => self::abbreviationStateHtml($get('timpa_singkatan') && filled($get('singkatan')) ? trim((string) $get('singkatan')) : null)),
                    ]),
                // Pratinjau langsung: the number this format gives today, before saving.
                Placeholder::make('preview')
                    ->label('Contoh nomor')
                    ->content(fn (Get $get) => self::previewHtml((string) $get('format'), (string) ($get('mode_bulan') ?: 'arab'), $get('timpa_singkatan') && filled($get('singkatan')) ? trim((string) $get('singkatan')) : null)),
                // Panduan token: what each token means, for staff who rarely set this up.
                Section::make('Panduan token')
                    ->icon('heroicon-o-question-mark-circle')
                    ->collapsible()
                    ->collapsed()
                    ->compact()
                    ->schema([
                        Placeholder::make('guide')->hiddenLabel()->content(fn () => self::guideHtml()),
                    ]),
            ])
            ->action(function (PersuratanMaster $record, array $data, array $arguments) {
                if ($arguments['reset'] ?? false) {
                    NomorFormatSettings::save($record, null, 'arab', null);
                    Notification::make()->success()
                        ->title($record->nama . ' memakai format bawaan')
                        ->body('Contoh: ' . NomorFormat::preview(NomorFormat::DEFAULT))
                        ->send();

                    return;
                }

                $format = trim($data['format']);
                // The default written out is the same as no custom format.
                NomorFormatSettings::save($record, $format, $data['mode_bulan'], ($data['timpa_singkatan'] ?? false) && filled($data['singkatan'] ?? null) ? trim($data['singkatan']) : null);

                Notification::make()->success()
                    ->title('Format ' . $record->nama . ' disimpan')
                    ->body('Contoh: ' . NomorFormatSettings::preview($record->fresh()))
                    ->send();
            });
    }

    /** Tombol penimpaan singkatan: ganti {S} satu jenis surat tanpa membuka editor format. */
    public static function abbreviation(): Action
    {
        return Action::make('abbreviation')
            ->label(fn (PersuratanMaster $record) => $record->singkatan_unit_kerja ? 'Ubah singkatan' : 'Timpa singkatan')
            ->icon('heroicon-m-pencil-square')
            ->color('gray')
            ->visible(fn ($livewire) => ($livewire->activeTab ?? null) === ListPersuratanMasters::NUMBERING_TAB)
            ->modalHeading(fn (PersuratanMaster $record) => 'Singkatan unit kerja ' . $record->nama)
            ->modalDescription(fn () => 'Kosongkan untuk kembali memakai singkatan dari Pengaturan Aplikasi (' . SuratKeluarNumber::kodeSatker() . ').')
            ->modalSubmitActionLabel('Simpan singkatan')
            ->fillForm(fn (PersuratanMaster $record) => ['singkatan' => $record->singkatan_unit_kerja])
            ->form([
                TextInput::make('singkatan')
                    ->label('Singkatan khusus')
                    ->placeholder(fn () => SuratKeluarNumber::kodeSatker())
                    ->maxLength(30)
                    ->extraInputAttributes(['class' => 'font-mono', 'autocomplete' => 'off'])
                    ->rules([fn () => function (string $attribute, $value, \Closure $fail) {
                        if (filled($value) && ($problem = NomorFormatSettings::singkatanProblem(trim((string) $value)))) {
                            $fail($problem);
                        }
                    }]),
            ])
            ->action(function (PersuratanMaster $record, array $data) {
                $settings = NomorFormatSettings::for($record);
                $singkatan = filled($data['singkatan'] ?? null) ? trim($data['singkatan']) : null;
                NomorFormatSettings::save($record, $settings['format'], $settings['mode_bulan'], $singkatan);

                Notification::make()->success()
                    ->title($singkatan ? 'Singkatan ' . $record->nama . ' ditimpa' : $record->nama . ' memakai singkatan dari Pengaturan Aplikasi')
                    ->body('Contoh: ' . NomorFormatSettings::preview($record->fresh()))
                    ->send();
            });
    }

    /** The {S} value in force while editing: the override, or the settings value. */
    public static function abbreviationStateHtml(?string $override): HtmlString
    {
        $value = $override ?? SuratKeluarNumber::kodeSatker();
        $source = $override !== null ? 'khusus jenis surat ini' : 'dari Pengaturan Aplikasi';

        return new HtmlString('<span data-abbreviation-state="' . ($override !== null ? 'override' : 'default') . '" class="font-mono font-semibold">' . e($value) . '</span>'
            . ' <span class="text-xs text-gray-500 dark:text-gray-400">(' . $source . ')</span>');
    }

    /** Contoh nomor for the editor, or why the format cannot be used yet. */
    public static function previewHtml(string $format, string $modeBulan, ?string $singkatan = null): HtmlString
    {
        $format = trim($format);
        $problem = match (true) {
            $format === '' => 'Tulis format atau pilih salah satu pilihan cepat.',
            (bool) NomorFormat::unknownTokens($format) => 'Ada token yang tidak dikenal.',
            ! in_array('N', NomorFormat::tokens($format), true) => 'Tambahkan {N} (nomor urut).',
            default => null,
        };

        return new HtmlString($problem
            ? '<span data-number-preview class="text-sm text-warning-600 dark:text-warning-400">' . e($problem) . '</span>'
            : '<span data-number-preview class="font-mono text-base font-semibold text-primary-600 dark:text-primary-400">' . e(NomorFormat::preview($format, $modeBulan, $singkatan)) . '</span>'
                . '<span class="block text-xs text-gray-500 dark:text-gray-400">Nomor urut, klasifikasi, dan tanggal memakai contoh; tanggal mengikuti hari ini.'
                . (in_array('S', NomorFormat::tokens($format), true) ? ' {S} = ' . e($singkatan ?? SuratKeluarNumber::kodeSatker()) . ($singkatan !== null ? ' (khusus jenis surat ini).' : ' (Pengaturan Aplikasi).') : '')
                . '</span>');
    }

    /** Panduan token: one row per token with its meaning and an example. */
    public static function guideHtml(): HtmlString
    {
        $rows = collect(NomorFormat::TOKENS)->map(fn (array $t, string $token) => sprintf(
            '<tr class="border-t border-gray-200 dark:border-white/10" data-token="%1$s"><td class="font-mono font-semibold text-primary-600 dark:text-primary-400" style="padding:.375rem .75rem .375rem 0;white-space:nowrap">{%1$s}</td><td style="padding:.375rem .75rem"><span class="font-medium text-gray-950 dark:text-white">%2$s</span><span class="block text-xs text-gray-500 dark:text-gray-400">%3$s</span></td><td class="font-mono text-gray-700 dark:text-gray-200" style="padding:.375rem 0 .375rem .75rem">%4$s</td></tr>',
            e($token), e($t['label']), e($t['description']), e($t['example']),
        ))->join('');

        return new HtmlString('<table class="w-full text-sm" style="border-collapse:collapse"><thead><tr class="text-left text-xs text-gray-500 dark:text-gray-400"><th style="padding:0 .75rem .375rem 0;font-weight:500">Token</th><th style="padding:0 .75rem .375rem;font-weight:500">Arti</th><th style="padding:0 0 .375rem .75rem;font-weight:500">Contoh</th></tr></thead><tbody>' . $rows . '</tbody></table><p class="text-xs text-gray-500 dark:text-gray-400" style="margin-top:.5rem">Pisahkan token dengan garis miring (/). Teks lain, mis. B-, ditulis apa adanya.</p>');
    }
}
