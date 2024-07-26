<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClaseAjusteIndividual extends Model
{
    use HasFactory;

    protected $table = 'Clase_Ajuste_Individual';

    protected $primaryKey = 'Id_Clase_Ajuste_Individual';

    protected $fillable = [
        'Descripcion',
        'Tipo',
    ];
}
