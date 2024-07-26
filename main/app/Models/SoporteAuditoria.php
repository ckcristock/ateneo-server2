<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SoporteAuditoria extends Model
{
    use HasFactory;
    protected $table = 'Soporte_Auditoria';
    protected $primaryKey = 'Id_Soporte_Auditoria';

    protected $fillable = [
        'Id_Tipo_Soporte',
        'Tipo_Soporte',
        'Cumple',
        'Archivo',
        'Id_Auditoria',
        'Paginas',
    ];
}
