<?php

namespace App\Filament\Resources\SuratKeputusanResource\Pages;

use App\Exceptions\TicketActionException;
use App\Filament\Resources\SuratKeputusanResource;
use App\Filament\Resources\TicketResource;
use App\Models\SuratKeputusan;
use App\Models\Ticket;
use App\Services\SuratKeputusanService;
use App\Support\TicketLabels;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\Url;

/**
 * Registrasi Nomor SK (/cp/penomoran-sk/registrasi): Minta Nomor SK as a
 * page of its own, like Registrasi Layanan Loket, for links from outside
 * the register and small screens. Same form and rules as the register's
 * "Minta Nomor SK" modal; after the number is given, Rincian SK opens.
 * Opened from a ticket (Proses tiket > Minta Nomor SK), the applicant and
 * the ticket number are filled in.
 */
class RegisterSkNumber extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = SuratKeputusanResource::class;

    protected static string $view = 'filament.pages.form-page';

    protected static ?string $title = 'Registrasi Nomor SK';

    protected static ?string $breadcrumb = 'Registrasi';

    /** @var array<string, mixed> */
    public ?array $data = [];

    /** Permohonan terkait (?tiket=id): the ticket this SK is asked for, from Proses tiket. */
    #[Url(as: 'tiket')]
    public ?int $ticketId = null;

    public function mount(): void
    {
        abort_unless(SuratKeputusanResource::canCreate(), 403);

        $this->form->fill();

        // From a ticket: the applicant asks for the number, the SK keeps the ticket number and starts with the service's jenis SK.
        if ($ticket = $this->ticket()) {
            $draft = SuratKeputusanService::registrationDraft($ticket)['isian'];
            $this->form->fill([
                ...$this->form->getRawState(),
                ...\Illuminate\Support\Arr::only($draft, ['nama_peminta', 'keterangan', 'jenis_sk']),
            ]);
        }
    }

    /** The ticket of ?tiket=, when the officer may ask for an SK number for it (SuratKeputusanPolicy::createForTicket). */
    public function ticket(): ?Ticket
    {
        $ticket = $this->ticketId ? Ticket::with(['user:id,name', 'service:id,name,jenis_sk_id', 'service.jenisSk'])->find($this->ticketId) : null;

        return $ticket && auth()->user()->can('createForTicket', [SuratKeputusan::class, $ticket]) ? $ticket : null;
    }

    public function getSubheading(): ?string
    {
        return 'Nomor SK diberikan berurutan per tahun dengan format {N} TAHUN {YYYY}, terpisah dari nomor surat keluar. Data SK masih dapat dikoreksi lewat "Ubah" setelah nomor terbit.';
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Permohonan terkait')
                ->description('Nama peminta dan keterangan diisi dari permohonan ini; ubah bila perlu.')
                ->icon('heroicon-o-ticket')
                ->compact()
                ->visible(fn () => $this->ticket() !== null)
                ->schema([
                    Placeholder::make('tiket')->hiddenLabel()
                        ->content(fn () => ($ticket = $this->ticket()) ? new HtmlString(
                            '<a data-sk-ticket="' . e($ticket->ticket_number) . '" href="' . e(TicketResource::getUrl('view', ['record' => $ticket])) . '" class="font-semibold text-primary-600 hover:underline dark:text-primary-400">' . e($ticket->ticket_number) . '</a>'
                            . '<span class="text-sm text-gray-600 dark:text-gray-300"> · ' . e($ticket->service?->name ?? 'Layanan') . ' · ' . e($ticket->user?->name ?? 'Pemohon') . ' · ' . e(TicketLabels::status($ticket->status)) . '</span>'
                        ) : null),
                ]),
            ...ListSuratKeputusan::requestForm(),
        ])->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('submit')->label('Ambil nomor')->icon('heroicon-m-hashtag')->submit('submit'),
            // The next SK usually shares the date and jenis: the form stays open with those.
            Action::make('submitAnother')->label('Ambil & minta lagi')->color('gray')->action('submitAnother'),
            Action::make('cancel')->label('Kembali ke register')->color('gray')->url(SuratKeputusanResource::getUrl('index')),
        ];
    }

    public function submitAnother(): void
    {
        $this->submit(another: true);
    }

    public function submit(bool $another = false): void
    {
        abort_unless(SuratKeputusanResource::canCreate(), 403);

        $data = $this->form->getState();

        try {
            // Permohonan terkait: only a ticket the officer may see (ticket()).
            $sk = app(SuratKeputusanService::class)->reserve(Carbon::parse($data['tanggal_surat']), auth()->user(), [...$data, 'ticket_id' => $this->ticket()?->getKey()]);
        } catch (TicketActionException $e) {
            Notification::make()->title('Nomor SK tidak dapat diberikan')->body($e->getMessage())->danger()->send();

            return;
        }

        ListSuratKeputusan::numberGiven($sk)->send();

        if ($another) {
            $this->form->fill(['tanggal_surat' => $data['tanggal_surat'], 'jenis_sk' => $data['jenis_sk']]);

            return;
        }

        $this->redirect(SuratKeputusanResource::getUrl('view', ['record' => $sk]));
    }
}
