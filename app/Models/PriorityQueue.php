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
        'beat_count'
    ];
    protected $casts = [
        'payload' => 'array'
    ];

    //
}
