<?php

namespace App\Filament\Resources\ComplaintResource\Pages;

use App\Filament\Resources\ComplaintResource;
use App\Models\Complaint;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListComplaints extends ListRecords
{
    protected static string $resource = ComplaintResource::class;

    public function getTabs(): array
    {
        $newCount = fn (array $types) => Complaint::whereIn('complaint_type', $types)->where('status', 'submitted')->count() ?: null;

        return [
            'pengaduan' => Tab::make('Pengaduan')
                ->icon('heroicon-m-chat-bubble-left-right')
                ->badge($newCount(['complaint']))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('complaint_type', 'complaint')),
            'whistleblowing' => Tab::make('Whistleblowing')
                ->icon('heroicon-m-shield-exclamation')
                ->badge($newCount(['whistleblowing']))
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('complaint_type', 'whistleblowing')),
        ];
    }
}
