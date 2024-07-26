<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RetencionComprobante extends Model
{
    protected $table = 'Retencion_Comprobante';
    protected $primaryKey = 'Id_Retencion_Comprobante';
    protected $fillable = [
        'Id_Factura',
        'Id_Comprobante',
        'Id_Retencion',
        'Valor',
    ];
}
