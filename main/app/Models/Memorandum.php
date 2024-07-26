<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Memorandum extends Model
{
    protected $fillable = [
        'person_id',
        'details',
        'file',
        'level',
        'state',
        'approve_user_id',
        'memorandum_type_id'
    ];
    protected $table = 'memorandums';

    public function memorandumtype()
    {
        return $this->belongsTo(MemorandumTypes::class, 'memorandum_type_id');
    }

    public function person()
    {
        return $this->belongsTo(Person::class, 'person_id')->with('contractultimate', 'documentType')->fullName();
    }

    public function attentionCalls()
    {
        return $this->belongsToMany(AttentionCall::class)->with('person', 'user');
    }

    public function histories()
    {
        return $this->morphMany(History::class, 'historable')->with('user');
    }

    public function disciplinaryProcess()
    {
        return $this->hasOne(DisciplinaryProcess::class);
    }

    public function files()
    {
        return $this->hasMany(MemorandumFile::class, 'memorandum_id');
    }

    public function approveUser()
    {
        return $this->belongsTo(Usuario::class, 'approve_user_id')->with('person');
    }
}
