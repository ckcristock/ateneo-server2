<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayConfigurationCompany extends Model
{

    protected $guarded = ['id'];

    protected $casts = [
        'affect_transportation_assistance' => 'boolean',
        'vacations_31_pay' => 'boolean'
    ];

    protected $fillable = [
        'disability_percentage_id',
        'deductions_pay',
        'recurrent_pay',
        'pay_transportation_assistance',
        'affect_transportation_assistance',
        'vacations_31_pay',
        'company_id'
    ];

    protected $columns = ['id', 'disability_percentage_id'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    public function percentage()
    {
        return $this->belongsTo(DisabilityPercentage::class, 'disability_percentage_id', 'id');
    }

    public function scopeExclude($query, $value = [])
    {
        return $query->select(array_diff($this->columns, (array) $value));
    }
}
