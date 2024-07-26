<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentTypes extends Model
{
    protected $table = 'document_types';

    protected $fillable = [
        'code',
        'name',
        'dian_code',
        'status'
    ];
}
