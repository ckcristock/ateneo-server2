<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'Categoria_Nueva';
    public function categoriaNueva()
    {
        return $this->belongsTo(CategoriaNueva::class, 'Id_Categoria_Nueva', 'Id_Categoria_Nueva');
    }
}

