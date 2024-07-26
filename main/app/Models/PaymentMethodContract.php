<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethodContract extends Model
{
    use HasFactory;
    protected $table = "payment_methods_contracts";

    protected $fillable = [
        'name',
    ];
}
