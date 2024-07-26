<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CountableLiquidation extends Model
{
    protected $fillable = [
        'concept',
    ];

    public function accounts()
    {
        return $this->hasOne(CompanyCountableLiquidation::class)
            ->with([
                'accountPlan' => function ($query) {
                    $query->selectRaw('Id_Plan_Cuentas, Id_Plan_Cuentas as value, Codigo_Niif, Nombre_Niif as text');
                }
            ])
            ->where('company_id', Person::find(Auth()->user()->person_id)->company_worked_id);
    }
}
