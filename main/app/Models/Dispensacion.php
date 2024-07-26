<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dispensacion extends Model
{
    use HasFactory;

    protected $table = 'Dispensacion';
    protected $primaryKey = 'Id_Dispensacion';
    protected $fillable = [
        'Codigo',
        'Numero_Documento',
        'Tipo',
        'Cuota',
        'EPS',
        'Fecha_Formula',
        'Fecha_Actual',
        'Cantidad_Entregas',
        'Entrega_Actual',
        'Estado',
        'Observaciones',
        'Productos_Entregados',
        'Pendientes',
        'Tipo_Servicio',
        'CIE',
        'Doctor',
        'IPS',
        'Identificacion_Funcionario',
        'Id_Punto_Dispensacion',
        'Estado_Facturacion',
        'Estado_Auditoria',
        'Identificacion_Auditor',
        'Fecha_Asignado_Auditor',
        'Fecha_Auditado',
        'Identificacion_Facturador',
        'Fecha_Asignado_Facturador',
        'Id_Factura',
        'Fecha_Facturado',
        'Causal_No_Pago',
        'Facturador_Asignado',
        'Estado_Dispensacion',
        'Id_Correspondencia',
        'Estado_Correspondencia',
        'Codigo_Qr_Real',
        'Firma_Reclamante',
        'Id_Diario_Cajas_Dispensacion',
        'Acta_Entrega',
        'Id_Turnero',
        'Nombre_Prueba',
        'Codigo_Qr',
        'Id_Servicio',
        'Id_Tipo_Servicio',
        'Paciente',
        'Id_Dispensacion_Mipres'
    ];

    public function productos()
    {
        return $this->hasMany(ProductoDispensacion::class, 'Id_Dispensacion');
    }
}
