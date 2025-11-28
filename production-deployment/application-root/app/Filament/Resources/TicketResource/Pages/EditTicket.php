<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Services\WhatsAppService;

class EditTicket extends EditRecord
{
    protected static string $resource = TicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $ticket = $this->getRecord();

        // Check if the status has changed to 'completed'
        if ($ticket->isDirty('status') && $ticket->status === 'completed') {
            // Ensure the ticket has a user and a WhatsApp number
            if ($ticket->user && $ticket->user->whatsapp_number) {
                $whatsappService = new WhatsAppService();
                $message = "Halo {$ticket->user->name}! Layanan Anda dengan nomor tiket {$ticket->ticket_number} ({$ticket->service->name}) telah selesai. Silakan cek email Anda untuk informasi lebih lanjut.";
                $whatsappService->sendMessage($ticket->user->whatsapp_number, $message);
            }
        }
    }
}
