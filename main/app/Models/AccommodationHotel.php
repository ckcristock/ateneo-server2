<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccommodationHotel extends Model
{
    protected $table = 'accommodation_hotel';

    protected $fillable = [
        'accommodation_id',
        'hotel_id',
        'price',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function accommodation()
    {
        return $this->belongsTo(Accommodation::class);
    }
}
