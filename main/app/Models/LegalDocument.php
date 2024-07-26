<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LegalDocument extends Model
{
    protected $fillable = [
        'file',
        'disciplinary_process_id',
        'name',
        'type',
        'state',
        'motivo',
        'size'
    ];
}
