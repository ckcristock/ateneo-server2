<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductoDocInventarioAuditable extends Model
{
    use HasFactory;

    protected $table = 'Producto_Doc_Inventario_Auditable';

    protected $primaryKey = 'Id_Producto_Doc_Inventario_Fisico';

    protected $fillable = [
        'Id_Producto',
        'Id_Inventario_Nuevo',
        'Primer_Conteo',
        'Fecha_Primer_Conteo',
        'Segundo_Conteo',
        'Fecha_Segundo_Conteo',
        'Cierre_Inventario',
        'Funcionario_Cantidad_Auditada',
        'Id_Doc_Inventario_Auditable',
        'Lote',
        'Fecha_Vencimiento',
        'Actualizado',
    ];
}
