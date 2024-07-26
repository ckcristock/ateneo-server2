<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CountableIncome extends Model
{
    protected $table = 'countable_income';
    protected $fillable = [
        'concept',
        'type',
        'account_plan_id',
        'state',
        'editable',
        'company_id',
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
