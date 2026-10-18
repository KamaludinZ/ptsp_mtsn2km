<?php

namespace App\Filament\Pages\FrontDesk;

use App\Exceptions\TicketActionException;
use App\Filament\Resources\TicketResource;
use App\Filament\Resources\UserResource;
use App\Models\Service;
use App\Models\User;
use App\Services\FrontDeskService;
use App\Support\Applicants;
use App\Support\RoleAccess;
use App\Support\ServiceSummary;
use App\Support\TicketDocuments;
use App\Support\TicketLabels;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Actions\Action as NotificationAction;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

/**
 * A walk-in applicant's request registered at the counter on their behalf
 * (mode offline). The officer first picks the applicant's status: not yet
 * registered (the account is found by e-mail or WhatsApp number, or
 * created) or already registered (an existing applicant account is picked).
 */
class RegisterService extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-plus';

    protected static ?string $navigationGroup = 'Loket';

    protected static ?string $navigationLabel = 'Registrasi Layanan';

    protected static ?string $title = 'Registrasi Layanan Loket';

    protected static ?string $slug = 'loket/registrasi-layanan';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.form-page';

    /** @var array<string, mixed> */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->can('frontdesk.access');
    }

    public function getSubheading(): ?string
    {
        return 'Daftarkan permohonan pemohon yang datang langsung ke loket PTSP.';
    }

    /** Pilihan status pemohon walk-in: belum punya akun, atau sudah terdaftar. */
    public const APPLICANT_STATUSES = [
        'baru' => 'Belum terdaftar',
        'terdaftar' => 'Sudah terdaftar',
    ];

    public function mount(): void
    {
        $this->form->fill(['priority' => 'normal', 'applicant_status' => 'baru', 'applicant_type' => 'umum']);
    }

    public function form(Form $form): Form
    {
        $registered = fn (Get $get) => $get('applicant_status') === 'terdaftar';
        $new = fn (Get $get) => $get('applicant_status') !== 'terdaftar';
        // Nama, WhatsApp, email: isian petugas (belum terdaftar) atau salinan terkunci dari akun terpilih.
        $shown = fn (Get $get) => $new($get) || filled($get('applicant_id'));
        $lockedHint = fn (Get $get) => $registered($get) ? 'Dari akun' : null;
        $lockedIcon = fn (Get $get) => $registered($get) ? 'heroicon-m-lock-closed' : null;

        return $form
            ->statePath('data')
            ->schema([
                Section::make('Pemohon')
                    ->description(fn (Get $get) => $registered($get)
                        ? 'Pilih akun pemohon yang sudah terdaftar; permohonan masuk ke akun tersebut tanpa membuat akun baru.'
                        : 'Jika email atau nomor WhatsApp sudah terdaftar, permohonan masuk ke akun tersebut.')
                    ->columns(2)
                    ->schema([
                        ToggleButtons::make('applicant_status')
                            ->label('Status pemohon')
                            ->options(self::APPLICANT_STATUSES)
                            ->icons(['baru' => 'heroicon-m-user-plus', 'terdaftar' => 'heroicon-m-identification'])
                            ->inline()
                            ->required()
                            ->live()
                            // Berganti status: isian pemohon dari status sebelumnya tidak terbawa,
                            // sedangkan tipe pengguna dan isian Permohonan (layanan, keperluan,
                            // prioritas, berkas) tetap seperti yang sudah diisi.
                            ->afterStateUpdated(fn (Set $set) => collect(['applicant_id', 'applicant_name', 'applicant_phone', 'applicant_email'])->each(fn (string $field) => $set($field, null)))
                            ->helperText(fn (Get $get) => $registered($get)
                                ? 'Pemohon sudah punya akun: cari namanya, data diambil dari akun tersebut.'
                                : 'Pemohon baru atau belum tahu: akun dicari lewat email/WhatsApp, atau dibuat bila belum ada.')
                            ->columnSpanFull(),
                        // Belum terdaftar: kategori akun yang dibuat. Sudah terdaftar: penyaring
                        // akun pemohon. Keduanya juga menyaring layanan yang bisa diajukan.
                        Select::make('applicant_type')
                            ->label(fn (Get $get) => $registered($get) ? 'Tipe pengguna' : 'Kategori pemohon')
                            ->options(Applicants::options())
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (?string $state, Get $get, Set $set) use ($registered) {
                                if ($registered($get)) {
                                    $set('applicant_id', null);
                                    self::fillFromAccount($set, null);
                                }

                                // Layanan terpilih dipertahankan selama masih bisa diajukan tipe ini.
                                if (filled($get('service_id')) && ! array_key_exists((int) $get('service_id'), self::serviceOptions($state))) {
                                    $set('service_id', null);
                                }
                            })
                            ->helperText(fn (Get $get) => $registered($get) ? 'Daftar akun di sebelah hanya berisi pemohon bertipe ini.' : null),
                        Select::make('applicant_id')
                            ->label('Akun pemohon')
                            ->placeholder('Pilih atau ketik nama, email, WhatsApp')
                            ->options(fn (Get $get) => self::applicantOptions('', $get('applicant_type')))
                            ->searchable()
                            ->getSearchResultsUsing(fn (string $search, Get $get) => self::applicantOptions($search, $get('applicant_type')))
                            ->getOptionLabelUsing(fn ($value) => self::applicantLabel(User::find($value)))
                            ->noSearchResultsMessage('Tidak ada pemohon bertipe ini dengan nama, email, atau WhatsApp tersebut.')
                            ->required()
                            ->validationMessages(['required' => 'Pilih akun pemohon terdaftar dulu, atau ganti status pemohon ke Belum terdaftar.'])
                            // Pilihan dikirim dari peramban: tetap harus akun pemohon aktif bertipe terpilih.
                            ->rule(fn (Get $get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                                if (! self::applicantQuery($get('applicant_type'))->whereKey($value)->exists()) {
                                    $fail('Pilih akun pemohon aktif yang bertipe ' . RoleAccess::roleLabel((string) $get('applicant_type')) . '.');
                                }
                            })
                            ->live()
                            ->afterStateUpdated(fn (?string $state, Set $set) => self::fillFromAccount($set, filled($state) ? User::find($state) : null))
                            ->visible($registered),
                        // Belum terdaftar: diisi petugas. Sudah terdaftar: terisi dari akun terpilih
                        // dan terkunci (tidak ikut dikirim; permohonan memakai akunnya langsung).
                        TextInput::make('applicant_name')->label('Nama lengkap')->required($new)->maxLength(255)
                            ->disabled($registered)->hint($lockedHint)->hintIcon($lockedIcon)->visible($shown),
                        TextInput::make('applicant_phone')->label('Nomor WhatsApp')->tel()->required($new)->maxLength(20)->live(onBlur: true)
                            ->disabled($registered)->hint($lockedHint)->hintIcon($lockedIcon)->visible($shown),
                        TextInput::make('applicant_email')->label(fn (Get $get) => $registered($get) ? 'Email' : 'Email (opsional)')->email()->maxLength(255)->live(onBlur: true)
                            ->disabled($registered)->placeholder(fn (Get $get) => $registered($get) ? 'Akun ini tidak punya email' : null)
                            ->hint($lockedHint)->hintIcon($lockedIcon)->visible($shown),
                        Placeholder::make('applicant_summary')
                            ->label('Ringkasan pemohon')
                            ->visible(fn (Get $get) => $registered($get) && filled($get('applicant_id')))
                            ->content(fn (Get $get) => self::applicantSummary(User::find($get('applicant_id'))))
                            ->columnSpanFull(),
                        Placeholder::make('applicant_match')
                            ->label('Hasil pencarian akun')
                            ->visible(fn (Get $get) => $new($get) && (filled($get('applicant_phone')) || filled($get('applicant_email'))))
                            ->content(fn (Get $get) => self::matchNotice(app(FrontDeskService::class)->findApplicant([
                                'applicant_phone' => $get('applicant_phone'),
                                'applicant_email' => $get('applicant_email'),
                            ])))
                            ->columnSpanFull(),
                    ]),
                Section::make('Permohonan')
                    ->columns(2)
                    ->schema([
                        Select::make('service_id')
                            ->label('Layanan')
                            ->options(fn (Get $get) => self::serviceOptions($get('applicant_type')))
                            ->searchable()
                            ->required()
                            ->live()
                            // Pilihan dikirim dari peramban: tetap harus layanan yang terbuka untuk tipe ini.
                            ->in(fn (Get $get) => array_keys(self::serviceOptions($get('applicant_type'))))
                            ->validationMessages(['in' => 'Layanan ini tidak tersedia: sedang nonaktif atau tidak terbuka untuk tipe pemohon yang dipilih. Pilih layanan lain.'])
                            ->helperText(fn (Get $get) => 'Hanya layanan yang terbuka untuk ' . RoleAccess::roleLabel((string) ($get('applicant_type') ?: 'umum')) . '; ganti tipe pemohon untuk melihat layanan lain.')
                            ->noSearchResultsMessage('Tidak ada layanan dengan nama itu untuk tipe pemohon ini.')
                            ->columnSpanFull(),
                        Placeholder::make('service_summary')
                            ->label('Standar pelayanan')
                            ->visible(fn (Get $get) => filled($get('service_id')))
                            ->content(fn (Get $get) => ServiceSummary::html(Service::find($get('service_id'))))
                            ->columnSpanFull(),
                        Placeholder::make('service_templates')
                            ->label('Template berkas')
                            ->helperText('Cetakkan atau kirimkan template ini kepada pemohon bila belum dilengkapi.')
                            ->visible(fn (Get $get) => (bool) Service::find($get('service_id'))?->activeTemplates->isNotEmpty())
                            ->content(fn (Get $get) => ServiceSummary::templates(Service::find($get('service_id'))))
                            ->columnSpanFull(),
                        Textarea::make('description')->label('Keperluan / keterangan')->required()->maxLength(1000)->rows(4)->columnSpanFull(),
                        Select::make('priority')->label('Prioritas')->options(array_intersect_key(TicketLabels::PRIORITIES, array_flip(['normal', 'high', 'urgent'])))->required(),
                        FileUpload::make('files')
                            ->label('Berkas persyaratan')
                            ->multiple()
                            ->disk('local')
                            ->directory('ticket-files')
                            ->visibility('private')
                            ->storeFileNamesIn('file_names')
                            ->acceptedFileTypes(TicketDocuments::REQUIREMENT_MIME_TYPES)
                            ->maxSize(10240)
                            ->maxFiles(10)
                            ->helperText('PDF, JPG, PNG, DOC/DOCX. Maksimal 10 MB per berkas.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('submit')->label('Daftarkan permohonan')->submit('submit')->icon('heroicon-m-check'),
        ];
    }

    /**
     * Layanan aktif yang bisa didaftarkan di loket untuk tipe pengguna ini.
     *
     * @return array<int, string> id => nama
     */
    public static function serviceOptions(?string $type): array
    {
        return Service::requestable($type ?: 'umum', 'offline')
            ->availableFor($type ?: 'umum')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    /** Akun yang bisa dipilih petugas: pemohon aktif bertipe $type (aturan pemohon resmi). */
    public static function applicantQuery(?string $type): Builder
    {
        return Applicants::lookup(null, $type);
    }

    /**
     * Akun pemohon bertipe $type yang cocok dengan pencarian petugas (nama,
     * email, atau WhatsApp); tanpa kata kunci, 50 nama pertama.
     *
     * @return array<int, string> id => label
     */
    public static function applicantOptions(string $search, ?string $type = null): array
    {
        return Applicants::lookup($search, $type)
            ->limit(50)
            ->get()
            ->mapWithKeys(fn (User $user) => [$user->id => self::applicantLabel($user)])
            ->all();
    }

    /** "Pak Ahmad — 0812… · ahmad@contoh.id" agar dua pemohon bernama sama bisa dibedakan. */
    public static function applicantLabel(?User $user): ?string
    {
        if (! $user) {
            return null;
        }

        $contact = collect([$user->whatsapp_number, str_ends_with((string) $user->email, '@walkin.local') ? null : $user->email])->filter()->join(' · ');

        return $user->name . ($contact !== '' ? ' — ' . $contact : '');
    }

    /** Salin nama, WhatsApp, dan email akun terpilih ke isian terkunci (kosong bila tidak ada akun). */
    public static function fillFromAccount(Set $set, ?User $user): void
    {
        $set('applicant_name', $user?->name);
        $set('applicant_phone', $user?->whatsapp_number);
        // Email buatan loket (…@walkin.local) bukan email pemohon.
        $set('applicant_email', $user && ! str_ends_with((string) $user->email, '@walkin.local') ? $user->email : null);
    }

    /**
     * Sudah terdaftar: keterangan akun terpilih (hanya baca) agar petugas bisa
     * memastikan pemohonnya, dengan tautan perbaikan ke Pengguna › Pemohon
     * bagi yang boleh membukanya.
     */
    public static function applicantSummary(?User $user): ?View
    {
        if (! $user) {
            return null;
        }

        $tickets = $user->tickets()->latest('id')->get(['id', 'ticket_number']);

        // Nama, WhatsApp, dan email sudah tampil di isian terkunci di atasnya.
        return view('filament.pages.front-desk.applicant-summary', [
            'rows' => [
                'Tipe pengguna' => collect(Applicants::rolesOf($user))->map(fn (string $role) => RoleAccess::roleLabel($role))->join(', ') ?: '–',
                'Terdaftar sejak' => $user->created_at?->translatedFormat('j F Y') ?? '–',
                'Permohonan sebelumnya' => $tickets->isEmpty()
                    ? 'Belum pernah'
                    : $tickets->count() . ' permohonan, terakhir ' . $tickets->first()->ticket_number,
            ],
            'fixUrl' => UserResource::canViewAny()
                ? UserResource::getUrl('index', ['activeTab' => 'pemohon', 'tableSearch' => $user->email])
                : null,
        ]);
    }

    /**
     * Belum terdaftar: kabar apakah email/WhatsApp yang diketik sudah dipakai
     * sebuah akun (permohonan masuk ke sana) atau akun baru akan dibuat.
     */
    public static function matchNotice(?User $match): HtmlString
    {
        if (! $match) {
            return new HtmlString('<span class="text-sm text-gray-600 dark:text-gray-400">Belum ada akun dengan email atau nomor WhatsApp ini. Akun pemohon baru dibuat saat permohonan didaftarkan.</span>');
        }

        if ($problem = FrontDeskService::linkProblem($match)) {
            return new HtmlString('<span class="text-sm text-danger-600 dark:text-danger-400">' . e($problem) . '</span>');
        }

        return new HtmlString('<span class="text-sm text-gray-950 dark:text-white">Sudah terdaftar atas nama <strong>' . e(self::applicantLabel($match)) . '</strong>. '
            . 'Permohonan akan masuk ke akun tersebut; tidak ada akun baru yang dibuat.</span>');
    }

    public function submit(): void
    {
        $data = $this->form->getState();
        $names = $data['file_names'] ?? [];
        $files = collect($data['files'] ?? [])->mapWithKeys(fn ($path) => [$path => $names[$path] ?? basename($path)])->all();

        // Sudah terdaftar: permohonan masuk ke akun yang dipilih petugas, tanpa akun baru.
        $applicant = ($data['applicant_status'] ?? 'baru') === 'terdaftar'
            ? User::findOrFail($data['applicant_id'])
            : null;

        try {
            [$ticket, $link] = app(FrontDeskService::class)->registerWalkInLinked($data, $files, auth()->user(), $applicant);
        } catch (TicketActionException $e) {
            // Tidak ada nomor permohonan: isian tetap di form agar bisa diperbaiki lalu dikirim lagi.
            Notification::make()->title('Permohonan belum terdaftar')->body($e->getMessage())->danger()->persistent()->send();

            return;
        } catch (\Throwable $e) {
            report($e);
            Notification::make()->title('Permohonan belum terdaftar')
                ->body('Terjadi gangguan saat menyimpan; belum ada nomor permohonan. Coba kirim lagi, atau hubungi administrator bila berulang.')
                ->danger()->persistent()->send();

            return;
        }

        Notification::make()
            ->success()
            ->title('Permohonan terdaftar dengan nomor ' . $ticket->ticket_number)
            // Hasil penautan akun: terpilih, tertaut ke akun lama, atau akun baru.
            ->body($link->message() . ' Sampaikan nomor tiket kepada pemohon untuk melacak permohonannya.')
            ->actions([
                NotificationAction::make('receipt')->label('Cetak tanda terima')->url(route('tickets.receipt', $ticket))->openUrlInNewTab(),
            ])
            ->persistent()
            ->send();

        $this->redirect(TicketResource::getUrl('view', ['record' => $ticket]));
    }
}
