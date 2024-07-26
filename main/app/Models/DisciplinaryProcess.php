<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisciplinaryProcess extends Model
{
    protected $fillable = [
        'code',
        'person_id',
        'process_description',
        'date_of_admission',
        'date_end',
        'status',
        'file',
        'approve_user_id',
        'close_description',
        'memorandum_id',
        'title'
    ];

    public function person()
    {
        return $this->belongsTo(Person::class)->imageName();
    }

    public function personInvolved()
    {
        return $this->hasMany(PersonInvolved::class)->with('person');
    }

    public function legalDocuments()
    {
        return $this->hasMany(LegalDocument::class);
    }

    public function memorandum()
    {
        return $this->belongsTo(Memorandum::class);
    }

    public function histories()
    {
        return $this->morphMany(History::class, 'historable')->with('user');
    }

    public function closure()
    {
        return $this->hasOne(DisciplinaryClosure::class);
    }

    public function actions()
    {
        return $this->hasMany(DisciplinaryProcessAction::class)->with('actionType');
    }
}
