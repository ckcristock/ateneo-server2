<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductoFacturaVenta extends Model
{
    use HasFactory;
    protected $table = 'Producto_Factura_Venta';
    protected $primaryKey = 'Id_Producto_Factura_Venta';
    public $timestamps = false;

    protected $fillable = [
        'Id_Factura_Venta',
        'Id_Inventario',
        'Id_Inventario_Nuevo',
        'Id_Producto',
        'Lote',
        'Fecha_Vencimiento',
        'Cantidad',
        'Precio_Venta',
        'Descuento',
        'Impuesto',
        'Subtotal',
        'Id_Remision',
        'Invima',
        'producto',
    ];

    public function producto()
    {
        return $this->belongsTo(Product::class, 'Id_Producto', 'Id_Producto');
    }

    public function inventarioNuevo()
    {
        return $this->belongsTo(InventarioNuevo::class, 'Id_Inventario_Nuevo', 'Id_Inventario_Nuevo');
    }
}
