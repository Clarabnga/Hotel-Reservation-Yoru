<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $fillable = ['room_id', 'name', 'email', 'phone', 'check_in', 'check_out', 'total_price', 'status'];

    public function room(){
        return $this->belongsTo(Room::class);
    }
    //
}
