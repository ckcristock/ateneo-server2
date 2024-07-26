<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RetentionType extends Model
{
    protected $fillable = [
        'name',
        'percentage',
        'description',
        'state',
        'type',
        'account_plan_id',
    ];

    protected $casts = [
        'percentage' => 'decimal:2',
    ];

    public function accountPlan()
    {
        return $this->belongsTo(PlanCuentas::class, 'account_plan_id');
    }
}
