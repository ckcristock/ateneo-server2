<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankAccounts extends Model
{

    protected $fillable = [
        'name',
        'associated_account',
        'account_number',
        'balance',
        'status',
        'type',
        'description',
        'company_id',
    ];
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
