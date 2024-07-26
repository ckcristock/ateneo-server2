<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BenefitIncome extends Model
{

    protected $guarded = ['id'];
    protected $fillable = [
        'person_id',
        'countable_income_id',
        'value'
    ];

    public function ingreso()
    {
        return $this->belongsTo(CountableIncome::class, 'countable_income_id', 'id');

    }

    public function funcionario()
    {
        return $this->belongsTo(Person::class);
    }

    public function scopeObtener($query, Person $funcionario, $fechaInicio, $fechaFin)
    {
        return $query->where('person_id', $funcionario->id)->whereBetween('created_at', [$fechaInicio, $fechaFin])->with('ingreso')->get();
    }
}
