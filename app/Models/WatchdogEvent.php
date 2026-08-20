<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WatchdogEvent extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['watchdog_job_id', 'event_type', 'metadata'];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }

    public function job()
    {
        return $this->belongsTo(PriorityQueue::class, 'watchdog_job_id');
    }
}
