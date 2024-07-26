<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fee extends Model
{
    protected $fillable = [
        "payment_method_id",
        "bank_id",
        "agenda",
        "contract_id",
        "price",
        "reason",
        "observation",
        "quantity"
    ];
}
