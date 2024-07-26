<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductoRemision extends Model
{
    protected $table = 'Producto_Remision';

    protected $primaryKey = 'Id_Producto_Remision';

    protected $fillable = [
        'Id_Remision',
        'Id_Producto_Factura_Venta',
        'Id_Inventario',
        'Lote',
        'Fecha_Vencimiento',
        'Cantidad',
        'Id_Producto',
        'Nombre_Producto',
        'Cantidad_Total',
        'Precio',
        'Costo',
        'Descuento',
        'Impuesto',
        'Subtotal',
        'Total_Descuento',
        'Total_Impuesto',
        'Id_Inventario_Nuevo',
        'Costo_Actualizado',
    ];

    // Define la relación con la remisión
    public function remision()
    {
        return $this->belongsTo(Remision::class, 'Id_Remision', 'Id_Remision');
    }
}
