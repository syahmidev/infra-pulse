<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AlertResource\Pages;
use App\Models\Alert;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;

class AlertResource extends Resource
{
    protected static ?string $model = Alert::class;
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-bell-alert';
    protected static \UnitEnum|string|null $navigationGroup = 'Infrastructure';
    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        return (string) Alert::where('is_read', false)->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return Alert::where('is_read', false)->where('severity', 'critical')->exists()
            ? 'danger'
            : 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\Select::make('server_id')
                ->relationship('server', 'name')
                ->required(),
            Forms\Components\Select::make('severity')
                ->options([
                    'info'     => 'Info',
                    'warning'  => 'Warning',
                    'critical' => 'Critical',
                ])
                ->required(),
            Forms\Components\TextInput::make('type')
                ->required()
                ->maxLength(100),
            Forms\Components\Textarea::make('message')
                ->required()
                ->columnSpanFull(),
            Forms\Components\Toggle::make('is_read')
                ->label('Mark as read'),
            Forms\Components\DateTimePicker::make('triggered_at')
                ->required()
                ->default(now()),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('server.name')
                    ->label('Server')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('severity')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'critical' => 'danger',
                        'warning'  => 'warning',
                        'info'     => 'info',
                        default    => 'gray',
                    }),
                Tables\Columns\TextColumn::make('type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('message')
                    ->limit(60)
                    ->tooltip(fn (Alert $record): string => $record->message),
                Tables\Columns\IconColumn::make('is_read')
                    ->label('Read')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock'),
                Tables\Columns\TextColumn::make('triggered_at')
                    ->label('Triggered')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('triggered_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('severity')
                    ->options([
                        'critical' => 'Critical',
                        'warning'  => 'Warning',
                        'info'     => 'Info',
                    ]),
                Tables\Filters\SelectFilter::make('server')
                    ->relationship('server', 'name'),
                Tables\Filters\TernaryFilter::make('is_read')
                    ->label('Read status')
                    ->trueLabel('Read')
                    ->falseLabel('Unread'),
            ])
            ->actions([
                Action::make('markRead')
                    ->label('Mark Read')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (Alert $record): bool => ! $record->is_read)
                    ->action(fn (Alert $record) => $record->update(['is_read' => true])),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('markAllRead')
                        ->label('Mark as read')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['is_read' => true]))
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAlerts::route('/'),
            'create' => Pages\CreateAlert::route('/create'),
            'edit'   => Pages\EditAlert::route('/{record}/edit'),
        ];
    }
}
