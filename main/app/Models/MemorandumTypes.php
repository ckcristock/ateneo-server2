<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemorandumTypes extends Model
{
    protected $table = 'memorandum_types';
    protected $fillable = [
        'name',
        'status',
        'company_id'
    ];
}
