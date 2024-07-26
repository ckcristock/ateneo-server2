<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comprobante extends Model
{
    protected $table = 'Comprobante';

    protected $primaryKey = 'Id_Comprobante';

    protected $fillable = [
        'Id_Funcionario',
        'Id_Cliente',
        'Id_Proveedor',
        'Fecha_Comprobante',
        'Id_Forma_Pago',
        'Cheque',
        'Id_Cuenta',
        'Observaciones',
        'Notas',
        'Codigo',
        'Tipo',
        'Tipo_Movimiento',
        'Codigo_Qr',
        'Estado',
        'Funcionario_Anula',
        'Fecha_Anulacion'
    ];

    public function formaPago()
    {
        return $this->belongsTo(FormaPago::class);
    }
}
