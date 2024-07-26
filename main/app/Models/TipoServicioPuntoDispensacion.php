<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoServicioPuntoDispensacion extends Model
{
    use HasFactory;
    protected $table = 'Tipo_Servicio_Punto_Dispensacion';
    protected $primaryKey = 'Id_Tipo_Servicio_Punto_Dispensacion';

    protected $fillable = ['Id_Tipo_Servicio', 'Id_Punto_Dispensacion'];
}
