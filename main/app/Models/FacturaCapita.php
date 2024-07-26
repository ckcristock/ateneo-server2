<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacturaCapita extends Model
{
    protected $table = 'Factura_Capita';
    protected $primaryKey = 'Id_Factura_Capita';
    protected $fillable = [
        'Id_Cliente',
        'Identificacion_Funcionario',
        'Fecha_Documento',
        'Mes',
        'Observacion',
        'Codigo',
        'Id_Departamento',
        'Id_Punto_Dispensacion',
        'Id_Regimen',
        'Cuota_Moderadora',
        'Estado_Factura',
        'Codigo_Qr',
        'Estado_Radicacion',
        'Id_Resolucion',
        'Cufe',
        'ZipKey',
        'ZipBase64Bytes',
        'Procesada',
        'Nota_Credito',
        'Valor_Nota_Credito',
        'Funcionario_Nota',
        'Fecha_Nota',
    ];


    public function factura()
    {
        return $this->belongsTo(Factura::class, 'Id_Factura_Capita', 'Id_Factura_Capita');
    }
}
