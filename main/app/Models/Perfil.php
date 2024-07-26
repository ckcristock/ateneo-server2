<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Perfil extends Model
{
    protected $table = 'Perfil';
    protected $primaryKey = 'Id_Perfil';
    protected $fillable = [
        'Nombre',
        'Detalle',
        'Tablero'
    ];

    public function scopeAlias($q, $alias)
    {
        return $q->from($q->getQuery()->from . " as " . $alias);
    }
}
