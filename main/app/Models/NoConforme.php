<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NoConforme extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'No_Conforme';

    protected $primaryKey = 'Id_No_Conforme';

    protected $fillable = [
        'Fecha_registro',
        'Codigo',
        'Persona_Reporta',
        'Descripcion',
        'Factura',
        'Tipo',
        'Id_Remision',
        'Id_Acta_Recepcion_Compra',
        'Estado',
        'Codigo_Qr',
    ];

    protected $casts = [
        'Fecha_registro' => 'datetime',
    ];
}

