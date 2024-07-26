<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TravelExpenseHotel extends Model
{
    protected $guarded = ['id'];
    protected $fillable = [
        'travel_expense_id',
        'hotel_id',
        'n_night',
        'who_cancels',
        'accommodation',
        'breakfast',
        'total',
        'rate'
    ];

    public function accommodation()
    {
        return $this->belongsTo(Accommodation::class, 'accommodation');
    }

    public function hoteles()
    {
        return $this->belongsTo(Hotel::class, 'id', 'travel_expense_hotels')
            ->withPivot(
                'who_cancels',
                'n_night',
                'breakfast',
                'total',
                'breakfast',
                'rate',
                'accommodation'
            );
    }
}
