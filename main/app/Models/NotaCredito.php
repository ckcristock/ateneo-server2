<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotaCredito extends Model
{
    protected $table = 'Nota_Credito';
    protected $primaryKey = 'Id_Nota_Credito';
    public $timestamps = false; 

    protected $fillable = [
        'Observacion',
        'Id_Factura',
        'Codigo',
        'Fecha',
        'Codigo_Qr',
        'Identificacion_Funcionario',
        'Id_Cliente',
        'Estado',
        'Motivo_Rechazo',
        'Fecha_Anulacion',
        'Funcionario_Anula',
        'Id_Bodega_Nuevo',
        'Cude',
        'Procesada',
    ];

    public function productos()
    {
        return $this->hasMany(ProductoNotaCredito::class, 'Id_Nota_Credito');
    }
    public function funcionario()
    {
        return $this->belongsTo(Person::class, 'Identificacion_Funcionario', 'identifier');
    }
}
