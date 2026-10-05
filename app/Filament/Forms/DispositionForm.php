<?php

namespace App\Filament\Forms;

use App\Models\Ticket;
use App\Services\TicketService;
use App\Support\Persuratan;
use App\Support\ServiceDisposition;
use App\Support\TicketDocuments;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Support\HtmlString;

/**
 * Panel aksi disposisi, shared by the disposition queue and the ticket page:
 * recipients, instruction, signature model (with its file) and a note.
 */
class DispositionForm
{
    public static function schema(Ticket $ticket): array
    {
        $service = $ticket->service;

        return [
            CheckboxList::make('recipients')
                ->label('Diteruskan kepada')
                ->options(ServiceDisposition::RECIPIENTS)
                ->default($service?->disposition_roles ?? [])
                ->columns(2)
                ->bulkToggleable(),
            ToggleButtons::make('instruction_preset')
                ->label('Pilihan cepat instruksi')
                ->options(fn () => collect(Persuratan::options('instruksi_disposisi'))->mapWithKeys(fn (string $i) => [$i => $i])->all())
                ->inline()
                ->live()
                ->dehydrated(false)
                ->afterStateUpdated(fn (?string $state, Set $set) => $set('instruction', $state))
                ->visible(fn () => filled(Persuratan::options('instruksi_disposisi'))),
            PersuratanFields::instruksi()
                ->live(onBlur: true)
                ->afterStateUpdated(fn (?string $state, Set $set) => $set('instruction_preset', in_array($state, Persuratan::options('instruksi_disposisi'), true) ? $state : null)),
            Radio::make('signature_model')
                ->label('Model tanda tangan')
                ->options(ServiceDisposition::SIGNATURE_MODELS)
                ->default(ServiceDisposition::defaultSignatureModel($service))
                ->helperText(ServiceDisposition::recommendationHint($service))
                ->required()
                ->live(),
            TextInput::make('acknowledged_by')
                ->label('Telah didisposisi oleh')
                ->placeholder('mis. Kepala Madrasah — Drs. H. Ahmad')
                ->helperText('Pimpinan yang mendisposisi bila dicatat atas namanya. Keterangan tambahan tulis di Catatan.')
                ->default(fn () => auth()->user()?->name)
                ->maxLength(255)
                ->required(fn (Get $get) => $get('signature_model') === 'acknowledged_by')
                ->visible(fn (Get $get) => $get('signature_model') === 'acknowledged_by'),
            Placeholder::make('sheet')
                ->hiddenLabel()
                ->content(new HtmlString(sprintf(
                    '<a href="%s" target="_blank" class="text-sm font-semibold text-primary-600 dark:text-primary-400">Cetak / unduh lembar disposisi ↗</a>',
                    e(route('tickets.disposition-sheet', $ticket)),
                )))
                ->visible(fn (Get $get) => in_array($get('signature_model'), ['ttd_upload', 'tte_upload'], true)),
            FileUpload::make('signature_file')
                ->label(fn (Get $get) => $get('signature_model') === 'tte_upload' ? 'Berkas TTE' : 'Pindaian lembar disposisi ber-TTD')
                ->helperText('Boleh diunggah sekarang atau menyusul lewat menu Unggah berkas di halaman tiket.')
                ->disk('local')
                ->directory('ticket-files')
                ->visibility('private')
                ->storeFileNamesIn('signature_file_name')
                ->acceptedFileTypes(TicketDocuments::REQUIREMENT_MIME_TYPES)
                ->maxSize(10240)
                ->visible(fn (Get $get) => in_array($get('signature_model'), ['ttd_upload', 'tte_upload'], true)),
            Textarea::make('notes')->label('Catatan (opsional)')->maxLength(500)->rows(3),
        ];
    }

    /** Record the disposition from the submitted panel. */
    public static function submit(Ticket $ticket, array $data): void
    {
        app(TicketService::class)->dispose(
            $ticket,
            auth()->user(),
            $data['signature_model'],
            $data['recipients'] ?? [],
            $data['instruction'] ?? null,
            $data['notes'] ?? null,
            $data['signature_file'] ?? null,
            $data['signature_file_name'] ?? null,
            $data['acknowledged_by'] ?? null,
        );
    }
}
