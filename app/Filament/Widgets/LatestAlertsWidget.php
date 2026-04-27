<?php

namespace App\Filament\Widgets;

use App\Models\Alert;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestAlertsWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    protected ?string $pollingInterval = '3s';
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'Latest Alerts';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Alert::query()
                    ->with('server')
                    ->where('is_read', false)
                    ->latest('triggered_at')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('triggered_at')
                    ->label('Time')
                    ->dateTime('M d, H:i:s')
                    ->sortable(),
                Tables\Columns\TextColumn::make('server.name')
                    ->label('Server')
                    ->sortable(),
                Tables\Columns\TextColumn::make('severity')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'critical' => 'danger',
                        'warning'  => 'warning',
                        'info'     => 'info',
                        default    => 'gray',
                    }),
                Tables\Columns\TextColumn::make('type')
                    ->label('Type'),
                Tables\Columns\TextColumn::make('message')
                    ->limit(80),
            ])
            ->actions([
                Action::make('markRead')
                    ->label('')
                    ->icon('heroicon-o-check')
                    ->tooltip('Mark as read')
                    ->color('success')
                    ->action(fn (Alert $record) => $record->update(['is_read' => true])),
            ])
            ->paginated(false);
    }
}
