<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentTicketsWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Tiket Terbaru')
            ->query(
                Ticket::query()
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('ticket_number')
                    ->label('No. Tiket')
                    ->searchable()
                    ->copyable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pemohon')
                    ->searchable()
                    ->limit(20),

                Tables\Columns\TextColumn::make('user.user_type')
                    ->label('Tipe')
                    ->badge()
                    ->colors([
                        'success' => fn ($state): bool => in_array($state, ['guru', 'pegawai']),
                        'info' => fn ($state): bool => in_array($state, ['siswa', 'alumni']),
                        'warning' => 'walimurid',
                        'secondary' => fn ($state): bool => in_array($state, ['instansi', 'umum']),
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'guru' => 'Guru',
                        'pegawai' => 'Pegawai',
                        'siswa' => 'Siswa',
                        'alumni' => 'Alumni',
                        'walimurid' => 'Wali Murid',
                        'instansi' => 'Instansi',
                        'umum' => 'Umum',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('service.name')
                    ->label('Layanan')
                    ->searchable()
                    ->limit(25),

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
                    ->label('Tanggal')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Belum Ada Tiket')
            ->emptyStateDescription('Belum ada tiket yang diajukan.')
            ->emptyStateIcon('heroicon-o-ticket');
    }
}
