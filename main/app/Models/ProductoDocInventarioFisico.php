<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductoDocInventarioFisico extends Model
{
    protected $table = 'Producto_Doc_Inventario_Fisico';
    protected $primaryKey = 'Id_Producto_Doc_Inventario_Fisico';
    public $timestamps = false;

    protected $fillable = [
        'Id_Producto',
        'Id_Inventario_Nuevo',
        'Primer_Conteo',
        'Fecha_Primer_Conteo',
        'Segundo_Conteo',
        'Fecha_Segundo_Conteo',
        'Cantidad_Auditada',
        'Funcionario_Cantidad_Auditada',
        'Cantidad_Inventario',
        'Id_Doc_Inventario_Fisico',
        'Lote',
        'Fecha_Vencimiento',
        'Actualizado'
    ];

    public function producto()
    {
        return $this->belongsTo(Product::class, 'Id_Producto', 'Id_Producto');
    }

    public function inventarioNuevo()
    {
        return $this->belongsTo(InventarioNuevo::class, 'Id_Inventario_Nuevo', 'Id_Inventario_Nuevo');
    }

    public function docInventarioFisico()
    {
        return $this->belongsTo(DocInventarioFisico::class, 'Id_Doc_Inventario_Fisico', 'Id_Doc_Inventario_Fisico');
    }
    
}
