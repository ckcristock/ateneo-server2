<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class PayrollManager extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'area',
    ];

    public function companyPayroll()
    {
        return $this->hasOne(CompanyPayrollManager::class)
            ->with([
                'person' => function ($query) {
                    $query->select(['id', 'identifier'])
                        ->selectRaw('CONCAT_WS(" ", first_name, first_surname) as text');
                }
            ])
            ->where('company_id', Person::find(Auth()->user()->person_id)->company_worked_id);
    }
}
