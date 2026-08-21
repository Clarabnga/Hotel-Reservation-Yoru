<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessInquiry extends Model
{
    protected $guarded = [];

    protected $casts = ['check_in' => 'date', 'check_out' => 'date'];
}
