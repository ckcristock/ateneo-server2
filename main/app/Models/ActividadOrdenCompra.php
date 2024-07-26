<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActividadOrdenCompra extends Model
{
    protected $table = 'Actividad_Orden_Compra';
    protected $primaryKey = 'Id_Actividad_Orden_Compra';
    protected $fillable = [
        'Id_Orden_Compra_Nacional',
        'Id_Acta_Recepcion_Compra',
        'Identificacion_Funcionario',
        'Fecha',
        'Detalles',
        'Estado'
    ];

    public function person()
    {
        return $this->belongsTo(Person::class, 'Identificacion_Funcionario')->fullName();
    }
}
