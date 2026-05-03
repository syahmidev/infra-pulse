<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Metric extends Model
{
    protected $fillable = [
        'server_id',
        'cpu_usage',
        'memory_usage',
        'memory_used',
        'memory_total',
        'disk_usage',
        'disk_used',
        'disk_total',
        'network_in',
        'network_out',
        'request_rate',
        'response_time',
    ];

    protected $casts = [
        'cpu_usage' => 'float',
        'memory_usage' => 'float',
        'disk_usage' => 'float',
        'network_in' => 'float',
        'network_out' => 'float',
        'request_rate' => 'float',
        'response_time' => 'float',
    ];

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }
}
