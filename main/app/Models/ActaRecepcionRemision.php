<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActaRecepcionRemision extends Model
{
    protected $table = 'Acta_Recepcion_Remision';

    public function remision()
    {
        return $this->belongsTo(Remision::class, 'Id_Remision');
    }

    public function people()
    {
        return $this->belongsTo(People::class, 'Identificacion_Funcionario', 'identifier');
    }
}
