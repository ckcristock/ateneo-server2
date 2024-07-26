<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentPayrollFactor extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_factor_id',
        'file',
        'name',
        'type'
    ];

    public function payrollFactor()
    {
        return $this->belongsTo(PayrollFactor::class);
    }
}
