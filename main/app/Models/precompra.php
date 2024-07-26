<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreCompra extends Model
{
    protected $table = 'Pre_Compra';
    protected $primaryKey = 'Id_Pre_Compra';
    protected $fillable = [
        'Identificacion_Funcionario',
        'Fecha',
        'Id_Orden_Pedido',
        'Id_Proveedor',
        'Estado',
        'Tipo',
        'Tipo_Medicamento',
        'Fecha_Inicio',
        'Fecha_Fin',
        'Excluir_Vencimiento',
        'Meses',
        'Id_Orden_Compra_Nacional',
        'Id_Contrato'
    ];

    public function scopeAlias($q, $alias)
    {
        return $q->from($q->getQuery()->from . " as " . $alias);
    }
}
