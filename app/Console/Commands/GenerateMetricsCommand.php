<?php

namespace App\Console\Commands;

use App\Jobs\GenerateServerMetrics;
use Illuminate\Console\Command;

class GenerateMetricsCommand extends Command
{
    protected $signature   = 'metrics:generate {--loop : Run continuously every 3 seconds}';
    protected $description = 'Generate mock server metrics and broadcast via Reverb';

    public function handle(): void
    {
        if ($this->option('loop')) {
            $this->info('Starting metrics loop (every 3s). Press Ctrl+C to stop.');
            while (true) {
                (new GenerateServerMetrics)->handle();
                sleep(3);
            }
        } else {
            (new GenerateServerMetrics)->handle();
            $this->info('Metrics generated and broadcast.');
        }
    }
}
