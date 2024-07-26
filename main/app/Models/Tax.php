<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tax extends Model
{
    protected $table = 'Impuesto';
    protected $primaryKey = 'Id_Impuesto';
    protected $fillable = ['Valor'];
}
