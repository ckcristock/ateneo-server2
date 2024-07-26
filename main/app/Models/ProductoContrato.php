<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductoContrato extends Model
{
    protected $table = 'Producto_Contrato';

    protected $primaryKey = 'Id_Producto_Contrato';

    protected $fillable = [
        'Id_Contrato',
        'Id_Producto',
        'Cum',
        'Cantidad',
        'Precio',
        'Precio_Anterior',
        'Ultima_Actualizacion',
        'Homologo',
    ];

}
