<?php

namespace App\Filament\Widgets;

use App\Models\Alert;
use App\Models\Server;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ServerStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected ?string $pollingInterval = '3s';
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $total    = Server::count();
        $online   = Server::where('status', 'online')->count();
        $warning  = Server::where('status', 'warning')->count();
        $critical = Server::where('status', 'critical')->count();
        $unread   = Alert::where('is_read', false)->count();

        return [
            Stat::make('Total Servers', $total)
                ->description('All registered servers')
                ->icon('heroicon-o-server')
                ->color('primary'),

            Stat::make('Online', $online)
                ->description('Servers running normally')
                ->icon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('Warning', $warning)
                ->description('Servers with elevated metrics')
                ->icon('heroicon-o-exclamation-triangle')
                ->color('warning'),

            Stat::make('Critical', $critical)
                ->description('Servers requiring immediate attention')
                ->icon('heroicon-o-x-circle')
                ->color('danger'),

            Stat::make('Unread Alerts', $unread)
                ->description('Alerts pending review')
                ->icon('heroicon-o-bell-alert')
                ->color($unread > 0 ? 'danger' : 'success'),
        ];
    }
}
