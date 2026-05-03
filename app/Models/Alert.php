<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alert extends Model
{
    protected $fillable = [
        'server_id',
        'type',
        'message',
        'severity',
        'is_read',
        'triggered_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'triggered_at' => 'datetime',
    ];

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }
}
