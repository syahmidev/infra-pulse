<?php

namespace App\Filament\Widgets;

use App\Models\Server;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ServerSummaryWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected ?string $pollingInterval = '3s';

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Server Overview';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Server::query()
                    ->where('is_active', true)
                    ->with('latestMetric')
                    ->orderBy('name')
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP'),
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
                Tables\Columns\TextColumn::make('latestMetric.disk_usage')
                    ->label('Disk %')
                    ->suffix('%')
                    ->numeric()
                    ->formatStateUsing(fn ($state) => number_format($state, 1))
                    ->color(fn ($state): string => match (true) {
                        $state >= 85 => 'warning',
                        default => 'success',
                    }),
                Tables\Columns\TextColumn::make('latestMetric.response_time')
                    ->label('Response (ms)')
                    ->suffix('ms')
                    ->numeric()
                    ->formatStateUsing(fn ($state) => number_format($state, 1))
                    ->color(fn ($state): string => match (true) {
                        $state >= 1000 => 'warning',
                        default => 'success',
                    }),
            ])
            ->paginated(false);
    }
}
