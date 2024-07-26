<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bodega extends Model
{
    use HasFactory;

    protected $table = 'Bodega_Nuevo';
    
    protected $primaryKey = 'Id_Bodega_Nuevo';
    
    protected $fillable = [
        'Nombre',
        'Direccion',
        'Telefono',
        'Mapa',
        'Compra_Internacional',
        'Estado',
        'Tipo',
        'company_id',
    ];

    public function ajustesIndividuales()
    {
        return $this->hasMany(AjusteIndividual::class, 'Id_Origen_Destino');
    }
}
