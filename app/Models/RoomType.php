<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomType extends Model
{
    protected $guarded = [];

    protected $casts = ['active' => 'boolean'];

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function getStartingPriceAttribute(): int
    {
        return (int) $this->rooms_min_price;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
