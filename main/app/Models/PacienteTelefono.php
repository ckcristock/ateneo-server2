<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PacienteTelefono extends Model
{
    use HasFactory;
    protected $table = 'Paciente_Telefono';
    protected $primaryKey = 'Id_Paciente_Telefono';
    public $timestamps = false;

    protected $fillable = [
        'Id_Paciente',
        'Numero_Telefono',
    ];
}
