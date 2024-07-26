<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DispensacionReclamante extends Model
{
    use HasFactory;
    protected $table = 'Dispensacion_Reclamante';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'Reclamante_Id',
        'Dispensacion_Id',
        'Parentesco',
    ];
}
