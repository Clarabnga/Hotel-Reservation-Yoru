<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PriorityQueue extends Model
{
    protected $fillable = [
        'priority',
        'payload',
        'job_class',
        'cut_count',
        'fail_count',
        'beat_count',
        'uuid',
        'idempotency_key',
        'base_priority',
        'current_priority',
        'queue_sequence',
        'attempt_count',
        'max_attempts',
        'status',
        'available_at',
        'last_started_at',
        'last_finished_at',
        'last_failed_at',
        'last_error',
    ];

    protected $casts = [
        'payload' => 'array',
        'available_at' => 'datetime',
        'last_started_at' => 'datetime',
        'last_finished_at' => 'datetime',
        'last_failed_at' => 'datetime',
    ];

    public function events()
    {
        return $this->hasMany(WatchdogEvent::class, 'watchdog_job_id');
    }

    //
}
