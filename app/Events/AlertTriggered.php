<?php

namespace App\Events;

use App\Models\Alert;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AlertTriggered implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Alert $alert) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('alerts'),
            new PrivateChannel("server.{$this->alert->server_id}"),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'id'           => $this->alert->id,
            'server_id'    => $this->alert->server_id,
            'server_name'  => $this->alert->server->name,
            'type'         => $this->alert->type,
            'message'      => $this->alert->message,
            'severity'     => $this->alert->severity,
            'triggered_at' => $this->alert->triggered_at,
        ];
    }
}
