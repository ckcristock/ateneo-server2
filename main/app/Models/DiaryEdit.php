<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiaryEdit extends Model
{
    protected $fillable = [
        'diariable_id',
        'diariable_type',
        'hours',
        'justification',
        'person_id'

    ];

    public function person()
    {
        return $this->belongsTo(Person::class)->fullName();
    }

    public function diariable()
    {
        return $this->morphTo();
    }
}
