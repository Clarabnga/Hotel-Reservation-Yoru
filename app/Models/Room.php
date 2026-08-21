<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'rooms';

    protected $maxReservationsPerMonth = 20;

    protected $fillable = ['id', 'room_type_id', 'number', 'type', 'price', 'facilities', 'status', 'operational_status', 'image'];

    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    public function getFormattedNumberAttribute()
    {
        return str_pad($this->number, 3, '0', STR_PAD_LEFT);
    }

    public function getFormattedPriceAttribute()
    {
        return 'Rp '.number_format($this->price, 0, ',', '.');
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function scopeActive($query)
    {
        return $query->where('operational_status', 'active');
    }

    public function isAvailableFor(string $checkIn, string $checkOut): bool
    {
        if ($this->operational_status !== 'active') {
            return false;
        }

        return ! $this->reservations()
            ->where('status', '!=', 'cancelled')
            ->where('check_in', '<', $checkOut)
            ->where('check_out', '>', $checkIn)
            ->exists();
    }

    public function isRoomBookedForMonth($year, $month)
    {
        $totalReservations = $this->reservations()
            ->whereYear('check_in', $year)
            ->whereMonth('check_in', $month)
            ->count();

        return $totalReservations >= $this->maxReservationsPerMonth;
    }

    //
}
