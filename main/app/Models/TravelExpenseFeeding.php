<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TravelExpenseFeeding extends Model
{
    protected $guarded = ['id'];
    protected $fillable = [
        'type',
        'breakfast',
        'rate',
        'travel_expense_id',
        'stay',
        'total'
    ];
}
