<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductoDispensacion extends Model
{
    use HasFactory;
    protected $table = 'Producto_Dispensacion';
    protected $primaryKey = 'Id_Producto_Dispensacion';
    public $timestamps = false;

    protected $fillable = [
        'Id_Dispensacion',
        'Id_Producto',
        'Id_Inventario',
        'Id_Inventario_Nuevo',
        'Costo',
        'Cum',
        'Cum_Autorizado',
        'Lote',
        'Id_Inventario_Nuevo_Seleccionados',
        'Cantidad_Formulada',
        'Cantidad_Entregada',
        'Numero_Autorizacion',
        'Fecha_Autorizacion',
        'Numero_Prescripcion',
        'Entregar_Faltante',
        'Fecha_Carga',
        'Cantidad_Formulada_Total',
        'Id_Producto_Mipres',
        'Id_Producto_Dispensacion_Mipres',
        'Actualizado',
        'Fecha_Actualizado',
        'Costo_Actualizado',
        'Generico',
    ];
}
