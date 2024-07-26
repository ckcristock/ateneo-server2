<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TravelExpenseAirTransport extends Model
{
    use HasFactory;
    protected $fillable = [
        'type',
        'journey',
        'company',
        'ticket_payment',
        'departure_date',
        'ticket_value',
        'travel_expense_id',
        'total'
    ];

}
