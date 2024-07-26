<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServicioPuntoDispensacion extends Model
{
    use HasFactory;

    protected $table = 'Servicio_Punto_Dispensacion';
    protected $primaryKey = 'Id_Servicio_Punto_Dispensacion';

    protected $fillable = ['Id_Servicio', 'Id_Punto_Dispensacion'];
}
