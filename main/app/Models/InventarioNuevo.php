<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventarioNuevo extends Model
{
    protected $table = 'Inventario_Nuevo';
    protected $primaryKey = 'Id_Inventario_Nuevo';
    protected $fillable = [
        'Codigo',
        'Id_Estiba',
        'Id_Producto',
        'Codigo_CUM',
        'Lote',
        'Fecha_Vencimiento',
        'Fecha_Carga',
        'Identificacion_Funcionario',
        'Id_Bodega',
        'Id_Punto_Dispensacion',
        'Cantidad',
        'Lista_Ganancia',
        'Id_Dispositivo',
        'Costo',
        'Cantidad_Apartada',
        'Estiba',
        'Fila',
        'Alternativo',
        'Actualizado',
        'Cantidad_Seleccionada',
        'Cantidad_Leo',
        'Negativo',
        'Cantidad_Pendientes',
    ];

    public function bodega()
    {
        return $this->belongsTo(BodegaNuevo::class, 'Id_Bodega', 'Id_Bodega_Nuevo');
    }

    public function estiba()
    {
        return $this->belongsTo(Estiba::class, 'Id_Estiba', 'Id_Estiba');
    }

    public function producto()
    {
        return $this->belongsTo(Product::class, 'Id_Producto', 'Id_Producto');
    }

    public function puntoDispensacion()
    {
        return $this->belongsTo(PuntoDispensacion::class, 'Id_Punto_Dispensacion', 'Id_Punto_Dispensacion');
    }

    public function funcionario()
    {
        return $this->belongsTo(Person::class, 'Identificacion_Funcionario', 'identifier');
    }
}
