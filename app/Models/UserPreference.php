<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPreference extends Model
{
    protected $guarded = [];

    protected $casts = ['airport_transfer' => 'boolean'];
}
