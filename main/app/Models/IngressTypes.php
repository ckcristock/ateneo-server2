<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IngressTypes extends Model
{
    protected $fillable = [
        'name',
        'associated_account',
        'type',
        'status'
    ];
    protected $table = 'ingress_types';

    public function account()
    {
        return $this->belongsTo(PlanCuentas::class, 'associated_account', 'Id_Plan_Cuentas');
    }
}
