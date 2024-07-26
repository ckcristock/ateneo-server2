<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductoAjusteIndividual extends Model
{
    use HasFactory;
    protected $table = 'Producto_Ajuste_Individual';

    protected $primaryKey = 'Id_Producto_Ajuste_Individual';

    protected $fillable = [
        'Id_Ajuste_Individual',
        'Id_Producto',
        'Id_Inventario',
        'Id_Inventario_Nuevo',
        'Lote',
        'Lote_Nuevo',
        'Fecha_Vencimiento',
        'Fecha_Vencimiento_Nueva',
        'Cantidad',
        'Costo',
        'Observaciones',
        'Id_Nueva_Estiba',
        'Id_Estiba_Acomodada',
    ];

    public function ajusteIndividual()
    {
        return $this->belongsTo(AjusteIndividual::class, 'Id_Ajuste_Individual', 'Id_Ajuste_Individual');
    }

    public function producto()
    {
        return $this->belongsTo(Product::class, 'Id_Producto', 'Id_Producto');
    }

    public function inventarioNuevo()
    {
        return $this->belongsTo(InventarioNuevo::class, 'Id_Inventario_Nuevo', 'Id_Inventario_Nuevo');
    }

    public function nuevaEstiba()
    {
        return $this->belongsTo(Estiba::class, 'Id_Nueva_Estiba', 'Id_Estiba');
    }

    public function estibaAcomodada()
    {
        return $this->belongsTo(Estiba::class, 'Id_Estiba_Acomodada', 'Id_Estiba');
    }
}
