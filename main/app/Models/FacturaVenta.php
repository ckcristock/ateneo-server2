<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacturaVenta extends Model
{
    protected $table = 'Factura_Venta';
    protected $primaryKey = 'Id_Factura_Venta';
    protected $fillable = [
        'Fecha_Documento',
        'Id_Cliente',
        'Id_Funcionario',
        'Id_Lista_Ganancia',
        'Observacion_Factura_Venta',
        'Codigo',
        'Estado',
        'Id_Cotizacion_Venta',
        'Condicion_Pago',
        'Fecha_Pago',
        'Codigo_Qr_Real',
        'Remisiones',
        'Funcionario_Anula',
        'Fecha_Anulacion',
        'Id_Causal_Anulacion',
        'Factura_Ventacol',
        'Codigo_Qr',
        'Id_Resolucion',
        'Cufe',
        'ZipKey',
        'ZipBase64Bytes',
        'Procesada',
        'Nota_Credito',
        'Valor_Nota_Credito',
        'Funcionario_Nota',
        'Fecha_Nota',
        'Id_Cliente2',
        'Observacion_Factura_Venta2',
    ];

    public function factura()
    {
        return $this->belongsTo(Factura::class, 'Id_Factura_Venta', 'Id_Factura_Venta');
    }
    public function thirdParty()
    {
        return $this->belongsTo(ThirdParty::class, 'Id_Cliente', 'id')->where('is_client', true);
    }
}
