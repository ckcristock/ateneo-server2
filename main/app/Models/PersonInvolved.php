<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonInvolved extends Model
{
    protected $fillable = [
        'observation',
        'disciplinary_process_id',
        'file',
        'file_type',
        'user_id',
        'state',
        'person_id',
    ];

    public function user()
    {
        return $this->belongsTo(Usuario::class)->select(['id', 'person_id'])->with('personImageName');
    }

    public function person()
    {
        return $this->belongsTo(Person::class)->imageName();
    }

    public function memorandumInvolved()
    {
        return $this->hasMany(MemorandumInvolved::class)->with('memorandum.person', 'personInvolved');
    }
}
