<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VisitorResource\Pages;
use App\Models\Visitor;
use Filament\Forms;
use Filament\Forms\Form;
use App\Exceptions\TicketActionException;
use App\Models\User;
use App\Services\FrontDeskService;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class VisitorResource extends Resource
{
    protected static ?string $model = Visitor::class;

    protected static ?string $navigationLabel = 'Buku Tamu';

    protected static ?string $navigationGroup = 'Loket';

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $modelLabel = 'tamu';

    protected static ?string $pluralModelLabel = 'Buku Tamu';

    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        $count = Visitor::whereDate('check_in_time', today())->whereNull('check_out_time')->count();

        return $count ? (string) $count : null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Tamu yang masih di lokasi';
    }

    public static function form(Form $form): Form
    {
        $creating = $form->getOperation() === 'create';

        return $form
            ->schema([
                Forms\Components\Section::make('Identitas tamu')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama lengkap')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->label('Nomor telepon / WhatsApp')
                            ->tel()
                            ->maxLength(20),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('institution')
                            ->label('Instansi / asal')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Kunjungan')
                    ->schema([
                        Forms\Components\Textarea::make('purpose')
                            ->label('Keperluan')
                            ->required()
                            ->maxLength(500)
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Select::make('person_to_meet_id')
                            ->label('Bertemu dengan')
                            ->options(fn () => User::query()
                                ->where('is_active', true)
                                ->whereIn('user_type', ['guru', 'pegawai'])
                                ->orderBy('name')
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->visible($creating),
                        Forms\Components\TextInput::make('person_to_meet')
                            ->label('Bertemu dengan')
                            ->maxLength(255)
                            ->hidden($creating),
                        Forms\Components\FileUpload::make('photo_path')
                            ->label('Foto tamu')
                            ->image()
                            ->disk('public')
                            ->directory('visitor-photos')
                            ->maxSize(2048)
                            ->visible($creating),
                        Forms\Components\DateTimePicker::make('check_in_time')
                            ->label('Waktu check-in')
                            ->required()
                            ->hidden($creating),
                        Forms\Components\DateTimePicker::make('check_out_time')
                            ->label('Waktu check-out')
                            ->hidden($creating),
                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan')
                            ->maxLength(500)
                            ->columnSpanFull()
                            ->hidden($creating),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Visitor $record) => $record->phone),
                Tables\Columns\TextColumn::make('institution')
                    ->label('Instansi')
                    ->searchable()
                    ->limit(40)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('purpose')
                    ->label('Keperluan')
                    ->searchable()
                    ->limit(50)
                    ->wrap(),
                Tables\Columns\TextColumn::make('person_to_meet')
                    ->label('Menemui')
                    ->searchable()
                    ->placeholder('–')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('check_in_time')
                    ->label('Masuk')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('check_out_time')
                    ->label('Keluar')
                    ->time('H:i')
                    ->placeholder('Masih di lokasi')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('check_in_time')
                    ->label('Tanggal kunjungan')
                    ->form([Forms\Components\DatePicker::make('date')->label('Tanggal kunjungan')])
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['date'] ?? null,
                        fn (Builder $query, $date): Builder => $query->whereDate('check_in_time', $date)
                    ))
                    ->indicateUsing(fn (array $data) => ($data['date'] ?? null) ? 'Tanggal: ' . \Illuminate\Support\Carbon::parse($data['date'])->translatedFormat('j F Y') : null),
            ])
            ->actions([
                Tables\Actions\Action::make('checkOut')
                    ->label('Check-out')
                    ->icon('heroicon-m-arrow-right-start-on-rectangle')
                    ->color('warning')
                    ->visible(fn (Visitor $record) => ! $record->check_out_time && auth()->user()->can('checkOut', $record))
                    ->action(function (Visitor $record) {
                        try {
                            app(FrontDeskService::class)->checkOut($record);
                            Notification::make()->title("{$record->name} sudah check-out.")->success()->send();
                        } catch (TicketActionException $e) {
                            Notification::make()->title($e->getMessage())->danger()->send();
                        }
                    }),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\Action::make('print')
                        ->label('Cetak kartu tamu')
                        ->icon('heroicon-m-printer')
                        ->url(fn (Visitor $record) => route('visitors.print', $record))
                        ->openUrlInNewTab(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('check_in_time', 'desc')
            ->emptyStateHeading('Belum ada tamu')
            ->emptyStateIcon('heroicon-o-user-group');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVisitors::route('/'),
            'create' => Pages\CreateVisitor::route('/create'),
            'view' => Pages\ViewVisitor::route('/{record}'),
            'edit' => Pages\EditVisitor::route('/{record}/edit'),
        ];
    }
}