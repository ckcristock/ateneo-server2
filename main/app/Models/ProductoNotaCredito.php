<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductoNotaCredito extends Model
{
    use HasFactory;

    protected $table = 'Producto_Nota_Credito';
    protected $primaryKey = 'Id_Producto_Nota_Credito';
    protected $fillable = [
        'Id_Nota_Credito',
        'Id_Inventario',
        'Id_Inventario_Nuevo',
        'Cantidad',
        'Precio_Venta',
        'Subtotal',
        'Impuesto',
        'Id_Producto',
        'Id_Motivo',
        'Lote',
        'Fecha_Vencimiento',
        'Observacion'
    ];
}
