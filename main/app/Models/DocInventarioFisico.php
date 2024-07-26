<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocInventarioFisico extends Model
{
    protected $table = 'Doc_Inventario_Fisico';

    protected $primaryKey = 'Id_Doc_Inventario_Fisico';

    public $timestamps = false;

    protected $fillable = [
        'Id_Estiba',
        'Fecha_Inicio',
        'Fecha_Fin',
        'Funcionario_Digita',
        'Funcionario_Cuenta',
        'Funcionario_Autorizo',
        'Productos_Correctos',
        'Productos_Diferencia',
        'Observaciones',
        'Estado',
        'Id_Inventario_Fisico_Nuevo',
        'Lista_Productos',
        'Funcionario_Anula',
        'Fecha_Anulacion',
        'Observaciones_Anulacion',
    ];

    protected $casts = [
        'Productos_Correctos' => 'array',
        'Productos_Diferencia' => 'array',
        'Lista_Productos' => 'array',
    ];

    public function estiba()
    {
        return $this->belongsTo(Estiba::class, 'Id_Estiba', 'Id_Estiba');
    }

    public function funcionarioDigita()
    {
        return $this->belongsTo(People::class, 'Funcionario_Digita');
    }

    public function funcionarioCuenta()
    {
        return $this->belongsTo(People::class, 'Funcionario_Cuenta');
    }
    public function inventarioFisicoNuevo()
    {
        return $this->belongsTo(InventarioFisicoNuevo::class, 'Id_Inventario_Fisico_Nuevo', 'Id_Inventario_Fisico_Nuevo');
    }

    public function productoDocInventarioFisico()
    {
        return $this->hasMany(ProductoDocInventarioFisico::class, 'Id_Doc_Inventario_Fisico', 'Id_Doc_Inventario_Fisico');
    }
}
