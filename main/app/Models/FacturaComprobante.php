<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacturaComprobante extends Model
{
    protected $table = 'Factura_Comprobante';
    protected $primaryKey = 'Id_Factura_Comprobante';
    protected $fillable = [
        'Id_Comprobante',
        'Factura',
        'Excenta',
        'Gravada',
        'Iva',
        'Total',
        'Neto_Factura',
        'Valor',
        'Id_Factura',
        'Id_Cuenta_Descuento',
        'ValorDescuento',
        'ValorMayorPagar',
        'Id_Cuenta_MayorPagar',
    ];
}
