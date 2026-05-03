<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServerResource\Pages;
use App\Models\Server;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ServerResource extends Resource
{
    protected static ?string $model = Server::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-server';

    protected static \UnitEnum|string|null $navigationGroup = 'Infrastructure';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Server Details')->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('hostname')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('ip_address')
                    ->label('IP Address')
                    ->required()
                    ->maxLength(45),
                Forms\Components\Select::make('environment')
                    ->options([
                        'production' => 'Production',
                        'staging' => 'Staging',
                        'development' => 'Development',
                    ])
                    ->required(),
            ])->columns(2),

            Section::make('Status')->schema([
                Forms\Components\Select::make('status')
                    ->options([
                        'online' => 'Online',
                        'warning' => 'Warning',
                        'critical' => 'Critical',
                        'offline' => 'Offline',
                    ])
                    ->required()
                    ->default('online'),
                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true)
                    ->inline(false),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull()
                    ->rows(3),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('hostname')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->copyable(),
                Tables\Columns\TextColumn::make('environment')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'production' => 'danger',
                        'staging' => 'warning',
                        'development' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'online' => 'success',
                        'warning' => 'warning',
                        'critical' => 'danger',
                        'offline' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('latestMetric.cpu_usage')
                    ->label('CPU %')
                    ->suffix('%')
                    ->numeric()
                    ->formatStateUsing(fn ($state) => number_format($state, 1))
                    ->sortable()
                    ->color(fn ($state): string => match (true) {
                        $state >= 90 => 'danger',
                        $state >= 75 => 'warning',
                        default => 'success',
                    }),
                Tables\Columns\TextColumn::make('latestMetric.memory_usage')
                    ->label('Mem %')
                    ->suffix('%')
                    ->numeric()
                    ->formatStateUsing(fn ($state) => number_format($state, 1))
                    ->color(fn ($state): string => match (true) {
                        $state >= 90 => 'danger',
                        $state >= 75 => 'warning',
                        default => 'success',
                    }),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'online' => 'Online',
                        'warning' => 'Warning',
                        'critical' => 'Critical',
                        'offline' => 'Offline',
                    ]),
                Tables\Filters\SelectFilter::make('environment')
                    ->options([
                        'production' => 'Production',
                        'staging' => 'Staging',
                        'development' => 'Development',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active only'),
            ])
            ->actions([
                EditAction::make(),
                Action::make('toggleActive')
                    ->label(fn (Server $record): string => $record->is_active ? 'Deactivate' : 'Activate')
                    ->icon(fn (Server $record): string => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn (Server $record): string => $record->is_active ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->action(fn (Server $record) => $record->update(['is_active' => ! $record->is_active])),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServers::route('/'),
            'create' => Pages\CreateServer::route('/create'),
            'edit' => Pages\EditServer::route('/{record}/edit'),
        ];
    }
}
