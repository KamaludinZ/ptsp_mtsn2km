<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ComplaintResource\Pages;
use App\Models\Complaint;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ComplaintResource extends Resource
{
    protected static ?string $model = Complaint::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationGroup = 'Complaint Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('complaint_type')
                    ->options([
                        'complaint' => 'Complaint',
                        'suggestion' => 'Suggestion',
                        'whistleblowing' => 'Whistleblowing',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('complainant_name'),
                Forms\Components\TextInput::make('complainant_contact'),
                Forms\Components\TextInput::make('complainant_email')
                    ->email(),
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable(),
                Forms\Components\Select::make('status')
                    ->options([
                        'submitted' => 'Submitted',
                        'in_review' => 'In Review',
                        'in_progress' => 'In Progress',
                        'resolved' => 'Resolved',
                        'closed' => 'Closed',
                    ])
                    ->required(),
                Forms\Components\Select::make('priority')
                    ->options([
                        'low' => 'Low',
                        'normal' => 'Normal',
                        'high' => 'High',
                        'urgent' => 'Urgent',
                    ])
                    ->default('normal'),
                Forms\Components\Select::make('assigned_to')
                    ->relationship('assignee', 'name')
                    ->searchable(),
                Forms\Components\Textarea::make('resolution_notes')
                    ->columnSpanFull(),
                Forms\Components\DateTimePicker::make('resolved_at'),
                Forms\Components\Select::make('resolved_by')
                    ->relationship('resolver', 'name')
                    ->searchable(),
                Forms\Components\Toggle::make('anonymous'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('complaint_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'complaint' => 'danger',
                        'suggestion' => 'success',
                        'whistleblowing' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('complainant_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'submitted' => 'gray',
                        'in_review' => 'info',
                        'in_progress' => 'warning',
                        'resolved' => 'success',
                        'closed' => 'secondary',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('priority')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'low' => 'success',
                        'normal' => 'info',
                        'high' => 'warning',
                        'urgent' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('assigned_to.name')
                    ->label('Assigned To')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ManageComplaints::route('/'),
        ];
    }
}