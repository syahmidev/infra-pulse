<?php

namespace App\Jobs;

use App\Events\AlertTriggered;
use App\Events\MetricUpdated;
use App\Models\Alert;
use App\Models\Metric;
use App\Models\Server;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;

class GenerateServerMetrics implements ShouldQueue
{
    use Queueable;

    private array $profiles = [
        'web-01'    => ['cpu_base' => 45, 'mem_gb' => 8,  'disk_gb' => 100, 'req_rate' => 120, 'cpu_swing' => 20, 'disk_pct_base' => 52],
        'web-02'    => ['cpu_base' => 35, 'mem_gb' => 8,  'disk_gb' => 100, 'req_rate' => 95,  'cpu_swing' => 15, 'disk_pct_base' => 48],
        'db-01'     => ['cpu_base' => 58, 'mem_gb' => 32, 'disk_gb' => 500, 'req_rate' => 40,  'cpu_swing' => 18, 'disk_pct_base' => 76],
        'cache-01'  => ['cpu_base' => 18, 'mem_gb' => 16, 'disk_gb' => 50,  'req_rate' => 200, 'cpu_swing' => 8,  'disk_pct_base' => 38],
        'worker-01' => ['cpu_base' => 76, 'mem_gb' => 4,  'disk_gb' => 80,  'req_rate' => 0,   'cpu_swing' => 18, 'disk_pct_base' => 82],
        'api-dev'   => ['cpu_base' => 25, 'mem_gb' => 4,  'disk_gb' => 50,  'req_rate' => 15,  'cpu_swing' => 30, 'disk_pct_base' => 41],
    ];

    public function handle(): void
    {
        $now = now();

        Server::where('is_active', true)->each(function (Server $server) use ($now) {
            $metric = $this->generateMetric($server, $now);
            $this->checkAlerts($server, $metric);
            $this->updateServerStatus($server, $metric);
            MetricUpdated::dispatch($metric, $server->status);
        });
    }

    private function generateMetric(Server $server, Carbon $now): Metric
    {
        $profile = $this->profiles[$server->name] ?? [
            'cpu_base' => 40, 'mem_gb' => 8, 'disk_gb' => 100, 'req_rate' => 50, 'cpu_swing' => 20, 'disk_pct_base' => 55,
        ];

        $gb = 1024 * 1024 * 1024;
        $timeAngle = ($now->timestamp % 3600) / 3600 * 2 * M_PI;

        $cpu = max(0.0, min(100.0, $profile['cpu_base'] + sin($timeAngle) * $profile['cpu_swing'] + rand(-5, 5)));

        $memTotal = $profile['mem_gb'] * $gb;
        $memPct   = max(20.0, min(95.0, 65 + sin($timeAngle * 0.7) * 15 + rand(-3, 3)));
        $memUsed  = (int) ($memTotal * $memPct / 100);

        $diskTotal = $profile['disk_gb'] * $gb;
        $diskPct   = max(30.0, min(95.0, $profile['disk_pct_base'] + rand(-2, 2)));
        $diskUsed  = (int) ($diskTotal * $diskPct / 100);

        $networkIn  = max(0.0, $profile['req_rate'] * 2000 + rand(-50000, 50000));
        $networkOut = max(0.0, $profile['req_rate'] * 8000 + rand(-100000, 100000));

        $requestRate  = max(0.0, $profile['req_rate'] + sin($timeAngle) * 30 + rand(-10, 10));
        $responseTime = max(5.0, 120 + ($cpu / 100) * 800 + rand(-20, 50));

        return Metric::create([
            'server_id'     => $server->id,
            'cpu_usage'     => round($cpu, 2),
            'memory_usage'  => round($memPct, 2),
            'memory_used'   => $memUsed,
            'memory_total'  => $memTotal,
            'disk_usage'    => round($diskPct, 2),
            'disk_used'     => $diskUsed,
            'disk_total'    => $diskTotal,
            'network_in'    => round($networkIn, 2),
            'network_out'   => round($networkOut, 2),
            'request_rate'  => round($requestRate, 2),
            'response_time' => round($responseTime, 2),
        ]);
    }

    private function checkAlerts(Server $server, Metric $metric): void
    {
        $checks = [
            [
                'condition' => $metric->cpu_usage >= 90,
                'type'      => 'cpu_spike',
                'severity'  => 'critical',
                'message'   => "CPU critical: {$metric->cpu_usage}% on {$server->name}",
            ],
            [
                'condition' => $metric->cpu_usage >= 75 && $metric->cpu_usage < 90,
                'type'      => 'cpu_high',
                'severity'  => 'warning',
                'message'   => "CPU high: {$metric->cpu_usage}% on {$server->name}",
            ],
            [
                'condition' => $metric->memory_usage >= 90,
                'type'      => 'memory_high',
                'severity'  => 'critical',
                'message'   => "Memory critical: {$metric->memory_usage}% on {$server->name}",
            ],
            [
                'condition' => $metric->disk_usage >= 85,
                'type'      => 'disk_full',
                'severity'  => 'warning',
                'message'   => "Disk usage high: {$metric->disk_usage}% on {$server->name}",
            ],
            [
                'condition' => $metric->response_time >= 1000,
                'type'      => 'high_response',
                'severity'  => 'warning',
                'message'   => "High response time: {$metric->response_time}ms on {$server->name}",
            ],
        ];

        foreach ($checks as $check) {
            if (! $check['condition']) {
                continue;
            }

            $alreadyFired = Alert::where('server_id', $server->id)
                ->where('type', $check['type'])
                ->where('triggered_at', '>=', now()->subMinutes(5))
                ->exists();

            if (! $alreadyFired) {
                $alert = Alert::create([
                    'server_id'    => $server->id,
                    'type'         => $check['type'],
                    'message'      => $check['message'],
                    'severity'     => $check['severity'],
                    'triggered_at' => now(),
                ]);

                AlertTriggered::dispatch($alert->load('server'));
            }
        }
    }

    private function updateServerStatus(Server $server, Metric $metric): void
    {
        $status = match (true) {
            $metric->cpu_usage >= 90 || $metric->memory_usage >= 90                                                => 'critical',
            $metric->cpu_usage >= 75 || $metric->memory_usage >= 80 || $metric->response_time >= 1000             => 'warning',
            default                                                                                                 => 'online',
        };

        if ($server->status !== $status) {
            $server->update(['status' => $status]);
        }
    }
}
