<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class MyTicketsWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Permohonan Terbaru Saya')
            ->query(
                Ticket::query()
                    ->where('user_id', auth()->id())
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('ticket_number')
                    ->label('No. Tiket')
                    ->searchable()
                    ->copyable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('service.name')
                    ->label('Layanan')
                    ->searchable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'secondary' => 'pending',
                        'warning' => 'processing',
                        'info' => 'verified',
                        'success' => 'completed',
                        'danger' => 'rejected',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Menunggu',
                        'processing' => 'Diproses',
                        'verified' => 'Terverifikasi',
                        'completed' => 'Selesai',
                        'rejected' => 'Ditolak',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Ajuan')
                    ->date('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('estimated_completion_date')
                    ->label('Estimasi Selesai')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('Lihat')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Ticket $record): string => route('onlineportal.track.ticket.form') . '?ticket=' . $record->ticket_number)
                    ->openUrlInNewTab(),
            ])
            ->emptyStateHeading('Belum Ada Permohonan')
            ->emptyStateDescription('Anda belum mengajukan permohonan layanan apapun.')
            ->emptyStateIcon('heroicon-o-document-text')
            ->emptyStateActions([
                Tables\Actions\Action::make('create')
                    ->label('Ajukan Layanan Baru')
                    ->url(route('onlineportal.service.catalog'))
                    ->icon('heroicon-o-plus')
                    ->button(),
            ]);
    }
}
