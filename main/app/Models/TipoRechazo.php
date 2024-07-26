<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoRechazo extends Model
{

    protected $table = 'Tipo_Rechazo';
    protected $primaryKey = 'Id_Tipo_Rechazo';
    protected $fillable = [
        'Nombre'
    ];
}
