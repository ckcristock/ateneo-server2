<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modulo extends Model
{
    use HasFactory;

    protected $table = 'Modulo';
    protected $primaryKey = 'Id_Modulo';
    protected $fillable = [
        'Nombre',
        'Documento',
        'Prefijo',
        'Estado',
    ];
}
