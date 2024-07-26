<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CountableDeduction extends Model
{
    protected $fillable = [
        'concept',
        'account_plan_id',
        'state',
        'type',
        'editable',
        'company_id'
    ];

    public function accounts()
    {
        return $this->belongsTo(AccountPlan::class, 'account_plan_id')
            ->selectRaw('Id_Plan_Cuentas, Id_Plan_Cuentas as value, Codigo_Niif, Nombre_Niif as text');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
