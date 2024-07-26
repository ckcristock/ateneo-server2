<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ElectronicPayrollPerson extends Model
{
    use HasFactory;

    protected $fillable = [
        'person_id',
        'person_payroll_payment_id',
        'cune',
        'report_date',
        'status',
        'dian_response',
        'payroll_code',
        'observation',
    ];

    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    public function personPayrollPayment()
    {
        return $this->belongsTo(PersonPayrollPayment::class);
    }
}
