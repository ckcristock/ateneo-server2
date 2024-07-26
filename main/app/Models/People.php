<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class People extends Model
{
    protected $table = 'people';
    protected $primaryKey = 'id';
    public $timestamps = true;

    // Relación con ActaRecepcionRemision
    public function actaRecepcionRemision()
    {
        return $this->hasMany(ActaRecepcionRemision::class, 'Identificacion_Funcionario');
    }

    public function bodegas()
    {
        return $this->belongsToMany(BodegaNuevo::class, 'funcionario_bodega_nuevo', 'Identificacion_Funcionario', 'Id_Bodega_Nuevo');
    }

    public function ajustesIndividuales()
    {
        return $this->hasMany(AjusteIndividual::class, 'Identificacion_Funcionario', 'identifier');
    }
    
    
}
