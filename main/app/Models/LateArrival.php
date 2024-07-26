<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LateArrival extends Model
{
    protected $fillable = [
        'date',
        'entry',
        'person_id',
        'real_entry',
        'time',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}
