<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CausalAnulacion extends Model
{
    protected $table = 'Causal_Anulacion';
    protected $primaryKey = 'Id_Causal_Anulacion';
    protected $fillable = [
        'Nombre'
    ];
}
