<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GrupoEstiba extends Model
{
    //use HasFactory;

    protected $table = 'Grupo_Estiba';

    protected $primaryKey = 'Id_Grupo_Estiba';

    protected $fillable = ['Id_Punto_Dispensacion', 'Nombre', 'Id_Bodega_Nuevo', 'Fecha_Vencimiento', 'Presentacion'];

    public function estibas()
    {
        return $this->hasMany(Estiba::class, 'Id_Grupo_Estiba', 'Id_Grupo_Estiba');
    }
}
