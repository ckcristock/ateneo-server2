<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductoOrdenCompraNacional extends Model
{
    protected $table = 'Producto_Orden_Compra_Nacional';
    protected $primaryKey = 'Id_Producto_Orden_Compra_Nacional';
    protected $fillable = [
        'Id_Orden_Compra_Nacional',
        'Id_Inventario',
        'Id_Producto',
        'Valor_Iva',
        'Subtotal',
        'impuesto_id',
        'Cantidad',
        'Total',
    ];

    public function product()
    {
        return $this->hasOne(Product::class, 'Id_Producto', 'Id_Producto')
            ->with('unit', 'packaging', 'tax');
    }

    public function tax()
    {
        return $this->belongsTo(Tax::class, 'impuesto_id', 'Id_Impuesto');
    }

    public function ordenCompraNacional()
    {
        return $this->hasOne(OrdenCompraNacional::class, 'Id_Orden_Compra_Nacional', 'Id_Orden_Compra_Nacional');
    }
}
