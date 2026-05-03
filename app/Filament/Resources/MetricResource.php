<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MetricResource\Pages;
use App\Models\Metric;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class MetricResource extends Resource
{
    protected static ?string $model = Metric::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static \UnitEnum|string|null $navigationGroup = 'Infrastructure';

    protected static ?int $navigationSort = 3;

    protected static ?string $label = 'Metric History';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('server.name')
                    ->label('Server')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('cpu_usage')
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
                Tables\Columns\TextColumn::make('memory_usage')
                    ->label('Mem %')
                    ->suffix('%')
                    ->numeric()
                    ->formatStateUsing(fn ($state) => number_format($state, 1))
                    ->sortable()
                    ->color(fn ($state): string => match (true) {
                        $state >= 90 => 'danger',
                        $state >= 75 => 'warning',
                        default => 'success',
                    }),
                Tables\Columns\TextColumn::make('disk_usage')
                    ->label('Disk %')
                    ->suffix('%')
                    ->numeric()
                    ->formatStateUsing(fn ($state) => number_format($state, 1))
                    ->sortable()
                    ->color(fn ($state): string => match (true) {
                        $state >= 85 => 'warning',
                        default => 'success',
                    }),
                Tables\Columns\TextColumn::make('network_in')
                    ->label('Net In (MB/s)')
                    ->numeric()
                    ->formatStateUsing(fn ($state) => number_format($state, 2))
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('network_out')
                    ->label('Net Out (MB/s)')
                    ->numeric()
                    ->formatStateUsing(fn ($state) => number_format($state, 2))
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('request_rate')
                    ->label('Req/s')
                    ->numeric()
                    ->formatStateUsing(fn ($state) => number_format($state, 1))
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('response_time')
                    ->label('Response (ms)')
                    ->numeric()
                    ->formatStateUsing(fn ($state) => number_format($state, 1))
                    ->suffix('ms')
                    ->sortable()
                    ->color(fn ($state): string => match (true) {
                        $state >= 1000 => 'warning',
                        default => 'success',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Recorded At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('server')
                    ->relationship('server', 'name'),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        DatePicker::make('from')->label('From'),
                        DatePicker::make('until')->label('Until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until'], fn ($q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->actions([])
            ->bulkActions([])
            ->paginated([25, 50, 100]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMetrics::route('/'),
        ];
    }
}
