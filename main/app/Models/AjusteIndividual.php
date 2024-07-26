<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AjusteIndividual extends Model
{
    protected $table = "Ajuste_Individual";

    protected $primaryKey = 'Id_Ajuste_Individual';

    protected $fillable = [
        'Codigo',
        'Fecha',
        'Identificacion_Funcionario',
        'Tipo',
        'Id_Clase_Ajuste_Individual',
        'Origen_Destino',
        'Id_Origen_Estiba',
        'Id_Origen_Destino',
        'Codigo_Qr',
        'Estado',
        'Observacion_Anulacion',
        'Funcionario_Anula',
        'Fecha_Anulacion',
        'Estado_Salida_Bodega',
        'Estado_Entrada_Bodega',
        'Funcionario_Autoriza_Salida',
        'Fecha_Aprobacion_Salida',
        'Cambio_Estiba',
        'Id_Salida',
    ];

    public function funcionario()
    {
        return $this->belongsTo(People::class, 'Identificacion_Funcionario', 'identifier');
    }
    public function bodega()
    {
        return $this->belongsTo(Bodega::class, 'Id_Origen_Destino');
    }

    public function claseAjuste()
    {
        return $this->belongsTo(ClaseAjusteIndividual::class, 'Id_Clase_Ajuste_Individual');
    }

    public function funcionarioAnula()
    {
        return $this->belongsTo(People::class, 'Funcionario_Anula', 'identifier');
    }

    public function funcionarioAutorizaSalida()
    {
        return $this->belongsTo(People::class, 'Funcionario_Autoriza_Salida', 'identifier');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
