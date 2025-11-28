<?php

namespace App\Filament\Widgets;

use Filament\Widgets\TableWidget;
use App\Models\Complaint;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LatestComplaintsWidget extends TableWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 4;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Complaint::with(['assignedTo'])
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('complaint_number')
                    ->label('Nomor')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('subject')
                    ->label('Subjek')
                    ->searchable()
                    ->limit(30)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'complaint' => 'warning',
                        'whistleblowing' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'complaint' => 'Pengaduan',
                        'whistleblowing' => 'Whistleblowing',
                        default => ucfirst($state),
                    })
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'investigating' => 'info',
                        'resolved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Menunggu',
                        'investigating' => 'Ditindaklanjuti',
                        'resolved' => 'Selesai',
                        'rejected' => 'Ditolak',
                        default => ucfirst($state),
                    })
                    ->toggleable(),
                Tables\Columns\TextColumn::make('assignedTo.name')
                    ->label('Ditugaskan Ke')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->url(fn (Complaint $record): string => route('filament.admin.resources.complaints.edit', ['record' => $record]))
                    ->icon('heroicon-m-eye')
                    ->button(),
            ])
            ->heading('Pengaduan Terbaru')
            ->paginated(false);
    }
}