<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BodegaNuevoCategoriaNueva extends Model
{
    protected $table = 'Bodega_Nuevo_Categoria_Nueva';
    protected $primaryKey = 'Id_Bodega_Nuevo_Categoria_Nueva';
    protected $fillable = [
        'Id_Bodega_Nuevo',
        'Id_Categoria_Nueva'
    ];
}
