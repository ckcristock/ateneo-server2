<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RrhhActivityPerson extends Model
{
    protected $fillable = [
        'person_id',
        'rrhh_activity_id',
        'state'
    ];

    public function person()
    {
        return $this->belongsTo(Person::class)->select(['id', DB::raw('CONCAT_WS(" ", first_name, first_surname) as text'), 'image']);
    }
}
