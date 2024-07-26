<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deduction extends Model
{

    protected $guarded = ['id'];

    protected $fillable = [
        'person_id',
        'countable_deduction_id',
        'value',
    ];
    public function scopePeriodo($query, Person $funcionario, $fechaInicio, $fechaFin)
    {
        return $query->where('person_id', '=', $funcionario->id)
            ->whereDate('created_at', '>=', $fechaInicio)
            ->whereDate('created_at', '<=', $fechaFin)
            /*   ->whereRaw(' DATE( created_at) ', [$fechaInicio, $fechaFin]) */
            ->with('deduccion')
            ->get();
    }

    public function deduccion()
    {
        return $this->belongsTo(CountableDeduction::class, 'countable_deduction_id', 'id');
    }
}
