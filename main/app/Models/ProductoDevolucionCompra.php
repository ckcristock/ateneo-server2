<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductoDevolucionCompra extends Model
{
    protected $table = 'Producto_Devolucion_Compra';

    public function devolucionCompra()
    {
        return $this->belongsTo(DevolucionCompra::class, 'Id_Devolucion_Compra', 'Id_Devolucion_Compra');
    }
}
