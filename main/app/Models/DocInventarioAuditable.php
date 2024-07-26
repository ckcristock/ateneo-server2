<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocInventarioAuditable extends Model
{
    protected $table = 'Doc_Inventario_Auditable';

    protected $primaryKey = 'Id_Doc_Inventario_Auditable';

    protected $fillable = [
        'Id_Bodega',
        'Fecha_Inicio',
        'Fecha_Fin',
        'Funcionario_Digita',
        'Funcionario_Cuenta',
        'Funcionario_Autorizo',
        'Productos_Correctos',
        'Productos_Diferencia',
        'Observaciones',
        'Estado',
        'Id_Inventario_Auditable_Nuevo',
        'Lista_Productos',
        'Funcionario_Anula',
        'Fecha_Anulacion',
        'Observaciones_Anulacion',
        'Fecha',
    ];

    protected $casts = [
        'Productos_Correctos' => 'array',
        'Productos_Diferencia' => 'array',
        'Lista_Productos' => 'array',
    ];

    public function bodegaNuevo()
    {
        return $this->belongsTo(BodegaNuevo::class, 'Id_Bodega', 'Id_Bodega_Nuevo');
    }

    public function funcionarioDigita()
    {
        return $this->belongsTo(People::class, 'Funcionario_Digita');
    }

    public function funcionarioCuenta()
    {
        return $this->belongsTo(People::class, 'Funcionario_Cuenta');
    }
}

