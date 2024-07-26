<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivoFijo extends Model
{
    protected $table = 'Activo_Fijo';
    protected $primaryKey = 'Id_Activo_Fijo';

    protected $fillable = [
        'Tipo',
        'Id_Tipo_Activo_Fijo',
        'Costo_NIIF',
        'Costo_PCGA',
        'Ultima_Adicion',
        'Fecha_Ultima_Adicion',
        'Nit',
        'Iva',
        'Base',
        'Iva_Niif',
        'Base_Niif',
        'Id_Centro_Costo',
        'Nombre',
        'Cantidad',
        'Documento',
        'Referencia',
        'Codigo',
        'Codigo_Activo_Fijo',
        'Concepto',
        'Tipo_Depreciacion',
        'Costo_Rete_Fuente',
        'Costo_Rete_Ica',
        'Costo_Rete_Fuente_NIIF',
        'Costo_Rete_Ica_NIIF',
        'Id_Cuenta_Rete_Ica',
        'Id_Cuenta_Rete_Fuente',
        'Identificacion_Funcionario',
        'Fecha',
        'Estado',
        'Funcionario_Anula',
        'Fecha_Anulacion',
        'Id_Descripcion_Factura_Administrativa',
        'Id_Empresa',
        'company_id',
    ];
}
