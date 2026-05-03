<?php

namespace App\Events;

use App\Models\Metric;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MetricUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Metric $metric,
        public string $status = 'online',
    ) {}

    public function broadcastAs(): string
    {
        return 'MetricUpdated';
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("server.{$this->metric->server_id}"),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'server_id' => $this->metric->server_id,
            'status' => $this->status,
            'cpu_usage' => $this->metric->cpu_usage,
            'memory_usage' => $this->metric->memory_usage,
            'memory_used' => $this->metric->memory_used,
            'memory_total' => $this->metric->memory_total,
            'disk_usage' => $this->metric->disk_usage,
            'disk_used' => $this->metric->disk_used,
            'disk_total' => $this->metric->disk_total,
            'network_in' => $this->metric->network_in,
            'network_out' => $this->metric->network_out,
            'request_rate' => $this->metric->request_rate,
            'response_time' => $this->metric->response_time,
            'recorded_at' => $this->metric->created_at,
        ];
    }
}
