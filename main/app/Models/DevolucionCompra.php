<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DevolucionCompra extends Model
{
    protected $table = 'Devolucion_Compra';

    public function productoDevolucionCompras()
    {
        return $this->hasMany(ProductoDevolucionCompra::class, 'Id_Devolucion_Compra', 'Id_Devolucion_Compra');
    }

    public function proveedor()
    {
        return $this->belongsTo(ThirdParty::class, 'Id_Proveedor', 'id');
    }

    public function Bodega()
    {
        return $this->belongsTo(BodegaNuevo::class, 'Id_Bodega_Nuevo', 'Id_Bodega_Nuevo');
    }

    public function people()
    {
        return $this->belongsTo(People::class, 'Identificacion_Funcionario', 'identifier');
    }

    public function productos()
    {
        return $this->hasMany(ProductoDevolucionCompra::class, 'Id_Devolucion_Compra');
    }

}
