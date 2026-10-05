<?php

namespace App\Filament\Pages\System;

use App\Models\AppSetting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/**
 * Pengaturan aplikasi in plain sections (identity, contact, links,
 * letterhead, site), writing the same settings the site already reads.
 */
class Settings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?string $navigationGroup = 'Manajemen Sistem';

    protected static ?string $navigationLabel = 'Pengaturan';

    protected static ?string $title = 'Pengaturan';

    protected static ?string $slug = 'pengaturan';

    protected static ?int $navigationSort = 0;

    protected static string $view = 'filament.pages.settings';

    /** key => [category, type, label] */
    public const FIELDS = [
        'app_name' => ['general', 'text', 'Nama singkat'],
        'app_name_full' => ['general', 'text', 'Nama lengkap instansi'],
        'app_tagline' => ['general', 'text', 'Slogan'],
        'app_logo' => ['branding', 'image', 'Logo'],
        'institution_npsn' => ['general', 'text', 'NPSN'],
        'institution_nsm' => ['general', 'text', 'NSM'],
        'headmaster_name' => ['general', 'text', 'Nama Kepala Madrasah'],
        'headmaster_nip' => ['general', 'text', 'NIP Kepala Madrasah'],
        'contact_address' => ['contact', 'textarea', 'Alamat'],
        'contact_phone' => ['contact', 'text', 'Telepon'],
        'contact_email' => ['contact', 'email', 'Email'],
        'contact_website' => ['contact', 'url', 'Situs web'],
        'contact_whatsapp_ptsp' => ['contact', 'text', 'WhatsApp PTSP'],
        'contact_whatsapp_pengaduan' => ['contact', 'text', 'WhatsApp pengaduan'],
        'operating_hours_weekday' => ['operating_hours', 'text', 'Senin–Kamis'],
        'operating_hours_friday' => ['operating_hours', 'text', 'Jumat'],
        'operating_hours_weekend' => ['operating_hours', 'text', 'Sabtu–Minggu'],
        'social_facebook' => ['social', 'url', 'Facebook'],
        'social_instagram' => ['social', 'url', 'Instagram'],
        'social_youtube' => ['social', 'url', 'YouTube'],
        'social_twitter' => ['social', 'url', 'X / Twitter'],
        'link_kemenag' => ['related_links', 'url', 'Kementerian Agama'],
        'link_kanwil' => ['related_links', 'url', 'Kanwil'],
        'link_kankemenag' => ['related_links', 'url', 'Kankemenag'],
        'ticket_prefix' => ['numbering', 'text', 'Awalan nomor tiket'],
        'surat_kode_satker' => ['numbering', 'text', 'Kode satker surat keluar'],
        'document_date_format' => ['numbering', 'text', 'Format tanggal dokumen'],
        'letterhead_line_1' => ['letterhead', 'text', 'Baris kop 1'],
        'letterhead_line_2' => ['letterhead', 'text', 'Baris kop 2'],
        'footer_description' => ['general', 'textarea', 'Deskripsi footer'],
        'copyright_text' => ['general', 'text', 'Teks hak cipta'],
        'enable_maintenance' => ['general', 'boolean', 'Mode perawatan'],
        'maintenance_message' => ['general', 'textarea', 'Pesan perawatan'],
    ];

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->can('kelola-pengaturan');
    }

    public function getSubheading(): ?string
    {
        return 'Identitas instansi, kontak, tautan, dan kop surat yang dipakai di situs, portal, dan dokumen cetak.';
    }

    public function mount(): void
    {
        $values = AppSetting::whereIn('key', array_keys(self::FIELDS))->pluck('value', 'key');
        $state = [];
        foreach (self::FIELDS as $key => [, $type]) {
            $value = $values[$key] ?? null;
            $state[$key] = match ($type) {
                'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
                'image' => is_string($value) && str_starts_with($value, 'storage/') ? substr($value, 8) : null,
                default => $value,
            };
        }
        $this->currentLogo = $values['app_logo'] ?? null;
        $this->form->fill($state);
    }

    public ?string $currentLogo = null;

    public function form(Form $form): Form
    {
        $text = fn (string $key) => Forms\Components\TextInput::make($key)->label(self::FIELDS[$key][2])->maxLength(255);
        $url = fn (string $key) => $text($key)->url()->placeholder('https://…');

        return $form->statePath('data')->schema([
            Forms\Components\Tabs::make('Bagian')->persistTabInQueryString('bagian')->tabs([
                Forms\Components\Tabs\Tab::make('Identitas')->icon('heroicon-m-building-library')->columns(2)->schema([
                    $text('app_name')->required()->helperText('Tampil di judul halaman dan menu, mis. PTSP MTsN 2 KOTA MALANG.'),
                    $text('app_name_full')->required(),
                    $text('app_tagline')->columnSpanFull(),
                    $text('institution_npsn')->regex('/^[0-9]{8}$/')->validationMessages(['regex' => 'NPSN terdiri dari 8 angka.']),
                    $text('institution_nsm')->regex('/^[0-9]{12}$/')->validationMessages(['regex' => 'NSM terdiri dari 12 angka.']),
                    $text('headmaster_name')->helperText('Untuk tanda tangan laporan. Kosongkan untuk memakai nama akun Kepala Madrasah.'),
                    $text('headmaster_nip')->regex('/^[0-9 ]{18,21}$/')->validationMessages(['regex' => 'NIP terdiri dari 18 angka.']),
                    Forms\Components\Placeholder::make('logo_preview')
                        ->label('Logo saat ini')
                        ->content(fn () => $this->currentLogo
                            ? new \Illuminate\Support\HtmlString('<img src="' . e(asset($this->currentLogo)) . '" alt="Logo saat ini" style="height: 72px; width: 72px; object-fit: contain;">')
                            : 'Belum ada logo.')
                        ->columnSpanFull(),
                    Forms\Components\FileUpload::make('app_logo')
                        ->label('Ganti logo')
                        ->image()
                        ->imageEditor()
                        ->imageCropAspectRatio('1:1')
                        ->disk('public')
                        ->directory('branding')
                        ->maxSize(2048)
                        ->helperText(fn () => 'PNG/JPG, persegi, maks. 2 MB.' . ($this->currentLogo && ! str_starts_with($this->currentLogo, 'storage/') ? ' Logo saat ini: ' . $this->currentLogo . '.' : ''))
                        ->columnSpanFull(),
                ]),
                Forms\Components\Tabs\Tab::make('Kontak & Jam Layanan')->icon('heroicon-m-phone')->columns(2)->schema([
                    Forms\Components\Textarea::make('contact_address')->label('Alamat')->rows(2)->columnSpanFull(),
                    $text('contact_phone')->tel(),
                    $text('contact_email')->email(),
                    $url('contact_website'),
                    $text('contact_whatsapp_ptsp')->tel()->placeholder('6281234567890'),
                    $text('contact_whatsapp_pengaduan')->tel()->placeholder('6281234567890'),
                    Forms\Components\Fieldset::make('Jam layanan')->columns(3)->schema([
                        $text('operating_hours_weekday')->placeholder('07.00–15.00'),
                        $text('operating_hours_friday')->placeholder('07.00–11.00'),
                        $text('operating_hours_weekend')->placeholder('Tutup'),
                    ]),
                ]),
                Forms\Components\Tabs\Tab::make('Media Sosial & Tautan')->icon('heroicon-m-link')->columns(2)->schema([
                    $url('social_facebook'), $url('social_instagram'), $url('social_youtube'), $url('social_twitter'),
                    $url('link_kemenag'), $url('link_kanwil'), $url('link_kankemenag'),
                ]),
                Forms\Components\Tabs\Tab::make('Format & Penomoran')->icon('heroicon-m-hashtag')->columns(2)->schema([
                    $text('ticket_prefix')
                        ->placeholder('PTSP')
                        ->regex('/^[A-Za-z]{2,8}$/')
                        ->validationMessages(['regex' => 'Awalan 2–8 huruf, tanpa angka atau spasi.'])
                        ->dehydrateStateUsing(fn (?string $state) => filled($state) ? strtoupper($state) : null)
                        ->helperText('Nomor tiket: AWALAN-TAHUNBULAN-URUT. Perubahan berlaku untuk tiket baru; nomor lama tetap.')
                        ->live(onBlur: true),
                    $text('surat_kode_satker')
                        ->placeholder('MTsN2KM')
                        ->regex('/^[A-Za-z0-9.\-]{2,20}$/')
                        ->validationMessages(['regex' => 'Kode satker 2–20 karakter: huruf, angka, titik, atau tanda hubung.'])
                        ->live(onBlur: true),
                    Forms\Components\Select::make('document_date_format')
                        ->label('Format tanggal dokumen')
                        ->options(\App\Support\Formats::DATE_FORMATS)
                        ->placeholder('5 Oktober 2026 (bawaan)')
                        ->helperText('Dipakai pada tanda terima, laporan, dan dokumen cetak lain.')
                        ->live(),
                    Forms\Components\Placeholder::make('numbering_preview')
                        ->label('Contoh')
                        ->content(fn (Forms\Get $get) => new \Illuminate\Support\HtmlString(
                            'Tiket: <strong>' . e(\App\Support\Formats::ticketNumber(7, now(), strtoupper($get('ticket_prefix') ?: 'PTSP'))) . '</strong><br>'
                            . 'Surat keluar: <strong>' . e(collect(['B-12', $get('surat_kode_satker') ?: 'MTsN2KM', 'PP.00', now()->format('m'), now()->format('Y')])->join('/')) . '</strong><br>'
                            . 'Tanggal: <strong>' . e(now()->translatedFormat($get('document_date_format') ?: 'j F Y')) . '</strong>'))
                        ->columnSpanFull(),
                ]),
                Forms\Components\Tabs\Tab::make('Kop Surat')->icon('heroicon-m-document-text')->schema([
                    $text('letterhead_line_1')->placeholder('Kementerian Agama Republik Indonesia'),
                    $text('letterhead_line_2')->placeholder('Kantor Kementerian Agama Kota Malang'),
                    Forms\Components\Placeholder::make('kop_hint')->hiddenLabel()
                        ->content('Kop dipakai pada laporan bulanan, lembar disposisi, dan dokumen cetak lain; di bawahnya tampil nama lengkap instansi dan kontak.'),
                ]),
                Forms\Components\Tabs\Tab::make('Situs')->icon('heroicon-m-globe-alt')->schema([
                    Forms\Components\Textarea::make('footer_description')->label('Deskripsi footer')->rows(2),
                    $text('copyright_text'),
                    Forms\Components\Toggle::make('enable_maintenance')->label('Mode perawatan')->live()
                        ->helperText('Situs publik menampilkan pesan perawatan; panel petugas tetap bisa dibuka.'),
                    Forms\Components\Textarea::make('maintenance_message')->label('Pesan perawatan')->rows(2)
                        ->visible(fn (Forms\Get $get) => (bool) $get('enable_maintenance')),
                ]),
            ]),
        ]);
    }

    public function save(): void
    {
        try {
            $state = $this->form->getState();
        } catch (\Illuminate\Validation\ValidationException $e) {
            Notification::make()->title('Pengaturan belum disimpan')->body('Periksa isian yang ditandai merah.')->danger()->send();

            throw $e;
        }

        $values = [];
        foreach (self::FIELDS as $key => [, $type]) {
            if (array_key_exists($key, $state)) {
                $values[$key] = $type === 'image' ? (filled($state[$key]) ? 'storage/' . $state[$key] : ($this->currentLogo ?: null)) : $state[$key];
            }
        }

        $previousLogo = $this->currentLogo;
        $changed = \App\Support\SettingsStore::save($values);
        $this->currentLogo = AppSetting::get('app_logo');
        if ($previousLogo !== $this->currentLogo) {
            \App\Support\SettingsStore::deleteUploadedLogo($previousLogo);
        }

        if (! $changed) {
            Notification::make()->title('Tidak ada perubahan')->info()->send();

            return;
        }

        Notification::make()->title('Pengaturan disimpan')->body(count($changed) . ' isian diperbarui.')->success()->send();
    }
}
