<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuestServiceRequest extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function watchdogJob()
    {
        return $this->belongsTo(PriorityQueue::class, 'watchdog_job_id');
    }
}
