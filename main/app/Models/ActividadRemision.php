<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActividadRemision extends Model
{
    protected $table = 'Actividad_Remision';
    protected $primaryKey = 'Id_Actividad_Remision';

    protected $fillable = [
        'Id_Remision',
        'Identificacion_Funcionario',
        'Fecha',
        'Detalles',
        'Estado'
    ];

    public function remision()
    {
        return $this->belongsTo(Remision::class, 'Id_Remision', 'Id_Remision');
    }

    public function funcionario()
    {
        return $this->belongsTo(Person::class, 'Identificacion_Funcionario', 'Identificacion_Funcionario');
    }
}
