<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttentionCall extends Model
{
    protected $fillable = [
        'reason',
        'number_call',
        'person_id',
        'user_id'
    ];

    protected $appends = ['details'];

    public function getDetailsAttribute()
    {
        return $this->attributes['reason'];
    }


    public function person()
    {
        return $this->belongsTo(Person::class, 'person_id')->with('contractultimate', 'documentType')->fullName();
    }

    public function user()
    {
        return $this->belongsTo(Usuario::class, 'user_id')->with('person');
    }

    public function memorandums()
    {
        return $this->belongsToMany(Memorandum::class);
    }

    public function histories()
    {
        return $this->morphMany(History::class, 'historable')->with('user');
    }
}
