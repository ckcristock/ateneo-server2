<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventarioContrato extends Model
{
    protected $table = 'Inventario_Contrato';

    protected $primaryKey = 'Id_Inventario_Contrato';

    protected $fillable = [
        'Id_Contrato',
        'Id_Inventario_Nuevo',
        'Id_Producto_Contrato',
        'Cantidad',
        'Cantidad_Apartada',
        'Cantidad_Seleccionada',
    ];

    public function contrato()
    {
        return $this->belongsTo(Contrato::class, 'Id_Contrato', 'Id_Contrato');
    }

    public function inventarioNuevo()
    {
        return $this->belongsTo(InventarioNuevo::class, 'Id_Inventario_Nuevo', 'Id_Inventario_Nuevo');
    }

    public function productoContrato()
    {
        return $this->belongsTo(ProductoContrato::class, 'Id_Producto_Contrato', 'Id_Producto_Contrato');
    }
}
