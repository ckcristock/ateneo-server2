<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dispensing extends Model
{
    protected $table = 'Punto_Dispensacion';

    protected $primaryKey = 'Id_Punto_Dispensacion';

    public $timestamps = false;

    protected $fillable = [
        'Nombre',
        'Tipo',
        'Tipo_Entrega',
        'Departamento',
        'Municipio',
        'Direccion',
        'Telefono',
        'Responsable',
        'No_Pos',
        'Turnero',
        'Cajas',
        'Wacom',
        'Entrega_Formula',
        'Entrega_Doble',
        'Autorizacion',
        'Tipo_Dispensacion',
        'Campo_Mipres',
        'Id_Bodega_Despacho',
        'Estado',
    ];

    public function municipality()
    {
        return $this->belongsTo(Municipality::class, 'Municipio', 'id');
    }
}

