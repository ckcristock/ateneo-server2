<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CupLaboratory extends Model
{
    protected $fillable = [
        'id_laboratory',
        'id_cup',
        'file',
        'state'
    ];
    public function laboratory()
    {
        return $this->belongsTo(Laboratories::class, 'id_laboratory', 'id');
    }
    public function cup()
    {
        return $this->belongsTo(Cup::class, 'id_cup', 'id')->with('colors');
    }
}
