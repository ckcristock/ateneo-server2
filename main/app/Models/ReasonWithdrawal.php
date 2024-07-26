<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReasonWithdrawal extends Model
{
    protected $table = 'reason_withdrawals';
    protected $fillable = [
        'name',
        'requirements',
    ];
}
