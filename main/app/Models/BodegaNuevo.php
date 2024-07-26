<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class BodegaNuevo extends Model
{
    protected $table = 'Bodega_Nuevo';

    protected $primaryKey = 'Id_Bodega_Nuevo';

    public function remisionesDestino(): MorphMany
    {
        return $this->morphMany(Remision::class, 'destino');
    }

    /**
     * Obtener todas las remisiones asociadas con esta bodega nueva como origen.
     */
    public function remisionesOrigen(): MorphMany
    {
        return $this->morphMany(Remision::class, 'origen');
    }

    // Define la relación con el modelo Estiba
    public function estibas()
    {
        return $this->hasMany(Estiba::class, 'Id_Bodega_Nuevo', 'Id_Bodega_Nuevo');
    }

    
}
