<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ComplaintResource\Pages;
use App\Models\Complaint;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ComplaintResource extends Resource
{
    protected static ?string $model = Complaint::class;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static ?string $navigationLabel = 'Pengaduan';

    protected static ?string $navigationGroup = 'Manajemen Pengaduan';

    protected static ?string $pluralModelLabel = 'Pengaduan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pengaduan')
                    ->schema([
                        Forms\Components\TextInput::make('complaint_number')
                            ->label('Nomor Pengaduan')
                            ->required()
                            ->unique(ignoreRecord: true),
                        Forms\Components\Select::make('type')
                            ->label('Jenis')
                            ->options([
                                'complaint' => 'Pengaduan',
                                'whistleblowing' => 'Whistleblowing',
                            ])
                            ->required(),
                        Forms\Components\Select::make('category')
                            ->label('Kategori')
                            ->options([
                                'layanan' => 'Layanan',
                                'pegawai' => 'Pegawai',
                                'fasilitas' => 'Fasilitas',
                                'prosedur' => 'Prosedur',
                                'lainnya' => 'Lainnya',
                            ])
                            ->required(),
                        Forms\Components\Select::make('priority')
                            ->label('Prioritas')
                            ->options([
                                'low' => 'Rendah',
                                'normal' => 'Normal',
                                'high' => 'Tinggi',
                                'urgent' => 'Darurat',
                            ])
                            ->default('normal')
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Menunggu',
                                'investigating' => 'Sedang Ditindaklanjuti',
                                'resolved' => 'Selesai',
                                'rejected' => 'Ditolak',
                            ])
                            ->default('pending')
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Detail Pengaduan')
                    ->schema([
                        Forms\Components\TextInput::make('subject')
                            ->label('Subjek')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('complaint_source')
                            ->label('Sumber Pengaduan')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Informasi Pemohon')
                    ->schema([
                        Forms\Components\TextInput::make('applicant_name')
                            ->label('Nama Pelapor')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('applicant_email')
                            ->label('Email Pelapor')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('applicant_phone')
                            ->label('Telepon Pelapor')
                            ->tel()
                            ->maxLength(20),
                        Forms\Components\TextInput::make('applicant_address')
                            ->label('Alamat Pelapor')
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Penanganan Pengaduan')
                    ->schema([
                        Forms\Components\Select::make('assigned_to')
                            ->label('Ditugaskan Ke')
                            ->relationship('assignedTo', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Textarea::make('resolution_notes')
                            ->label('Catatan Penyelesaian')
                            ->columnSpanFull(),
                        Forms\Components\DatePicker::make('resolved_at')
                            ->label('Tanggal Selesai'),
                        Forms\Components\Select::make('resolution_status')
                            ->label('Status Penyelesaian')
                            ->options([
                                'open' => 'Terbuka',
                                'in_progress' => 'Sedang Ditindaklanjuti',
                                'closed' => 'Ditutup',
                            ]),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('complaint_number')
                    ->label('Nomor')
                    ->searchable()
                    ->sortable(),
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
                    }),
                Tables\Columns\TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'layanan' => 'info',
                        'pegawai' => 'warning',
                        'fasilitas' => 'success',
                        'prosedur' => 'primary',
                        'lainnya' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'layanan' => 'Layanan',
                        'pegawai' => 'Pegawai',
                        'fasilitas' => 'Fasilitas',
                        'prosedur' => 'Prosedur',
                        'lainnya' => 'Lainnya',
                        default => ucfirst($state),
                    }),
                Tables\Columns\TextColumn::make('subject')
                    ->label('Subjek')
                    ->searchable()
                    ->limit(50),
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
                        'investigating' => 'Sedang Ditindaklanjuti',
                        'resolved' => 'Selesai',
                        'rejected' => 'Ditolak',
                        default => ucfirst($state),
                    }),
                Tables\Columns\TextColumn::make('priority')
                    ->label('Prioritas')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'low' => 'info',
                        'normal' => 'gray',
                        'high' => 'warning',
                        'urgent' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'low' => 'Rendah',
                        'normal' => 'Normal',
                        'high' => 'Tinggi',
                        'urgent' => 'Darurat',
                        default => ucfirst($state),
                    }),
                Tables\Columns\TextColumn::make('applicant_name')
                    ->label('Pelapor')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diubah')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Jenis')
                    ->options([
                        'complaint' => 'Pengaduan',
                        'whistleblowing' => 'Whistleblowing',
                    ]),
                Tables\Filters\SelectFilter::make('category')
                    ->label('Kategori')
                    ->options([
                        'layanan' => 'Layanan',
                        'pegawai' => 'Pegawai',
                        'fasilitas' => 'Fasilitas',
                        'prosedur' => 'Prosedur',
                        'lainnya' => 'Lainnya',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Menunggu',
                        'investigating' => 'Sedang Ditindaklanjuti',
                        'resolved' => 'Selesai',
                        'rejected' => 'Ditolak',
                    ]),
                Tables\Filters\SelectFilter::make('priority')
                    ->label('Prioritas')
                    ->options([
                        'low' => 'Rendah',
                        'normal' => 'Normal',
                        'high' => 'Tinggi',
                        'urgent' => 'Darurat',
                    ]),
                Tables\Filters\Filter::make('created_at')
                    ->form([Forms\Components\DatePicker::make('created_at')->label('Tanggal Dibuat')])
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['created_at'],
                        fn (Builder $query, $date): Builder => $query->whereDate('created_at', $date)
                    )),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListComplaints::route('/'),
            'create' => Pages\CreateComplaint::route('/create'),
            'view' => Pages\ViewComplaint::route('/{record}'),
            'edit' => Pages\EditComplaint::route('/{record}/edit'),
        ];
    }
}