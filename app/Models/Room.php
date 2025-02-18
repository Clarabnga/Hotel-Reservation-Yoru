<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;
    protected $fillable = ['number', 'type', 'price', 'facilities', 'status', 'image'];

    public function getFormattedNumberAttribute(){
        return str_pad($this->number, 3, '0', STR_PAD_LEFT);
    }

    public function getFormattedPriceAttribute(){
        return 'Rp ' . number_format($this->price, 0, ',','.');
    }
    public function reservations(){
        return $this->hasMany(Reservation::class);
    }
    //
}
