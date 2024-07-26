<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Liquidation extends Model
{
    protected $fillable = [
        'person_id',
        'motivo',
        'justa_causa',
        'fecha_contratacion',
        'fecha_terminacion',
        'dias_liquidados',
        'dias_vacaciones',
        'salario_base',
        'vacaciones_base',
        'cesantias_base',
        'dominicales_incluidas',
        'cesantias_anterior',
        'intereses_cesantias',
        'otros_ingresos',
        'prestamos',
        'otras_deducciones',
        'notas',
        'valor_dias_vacaciones',
        'valor_cesantias',
        'valor_prima',
        'sueldo_pendiente',
        'auxilio_pendiente',
        'otros',
        'salud',
        'pension',
        'total',
    ];

    public function person()
    {
        return $this->belongsTo(Person::class)->fullName();
    }
}
