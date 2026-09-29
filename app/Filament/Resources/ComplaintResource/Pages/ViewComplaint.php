<?php

namespace App\Filament\Resources\ComplaintResource\Pages;

use App\Filament\Concerns\NotifiesActionResult;
use App\Filament\Resources\ComplaintResource;
use App\Models\Complaint;
use App\Models\User;
use App\Services\ComplaintService;
use App\Support\RoleAccess;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\ViewRecord;

class ViewComplaint extends ViewRecord
{
    use NotifiesActionResult;

    protected static string $resource = ComplaintResource::class;

    public function getTitle(): string
    {
        return 'Laporan ' . $this->getRecord()->complaint_number;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('followUp')
                ->label('Tindak lanjut')
                ->icon('heroicon-m-arrow-path-rounded-square')
                ->visible(fn () => auth()->user()->can('update', $this->getRecord()))
                ->fillForm(fn () => [
                    'status' => $this->getRecord()->status,
                    'priority' => $this->getRecord()->priority ?? 'normal',
                    'assigned_to' => $this->getRecord()->assigned_to,
                    'response' => $this->getRecord()->response,
                    'resolution_notes' => $this->getRecord()->resolution_notes,
                ])
                ->form([
                    Select::make('status')->label('Tahap')->options(Complaint::STATUSES)->required(),
                    Select::make('priority')->label('Prioritas')->options(Complaint::PRIORITIES)->required(),
                    Select::make('assigned_to')->label('Penangan')
                        ->options(fn () => User::role(RoleAccess::COMPLAINT_HANDLERS)->orderBy('name')->pluck('name', 'id'))
                        ->searchable(),
                    Textarea::make('response')->label('Tanggapan kepada pelapor')->maxLength(5000)->rows(4)
                        ->helperText('Wajib diisi sebelum laporan ditandai selesai atau ditutup.'),
                    Textarea::make('resolution_notes')->label('Catatan internal')->maxLength(5000)->rows(3),
                ])
                ->action(function (array $data) {
                    self::attempt(fn () => app(ComplaintService::class)->followUp($this->getRecord(), $data, auth()->user()), 'Tindak lanjut disimpan.');
                    $this->record = $this->getRecord()->fresh(['assignee', 'service']);
                }),
            Actions\DeleteAction::make(),
        ];
    }
}
