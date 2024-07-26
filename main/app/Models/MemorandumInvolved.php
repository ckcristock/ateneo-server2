<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemorandumInvolved extends Model
{
    protected $fillable = [
        'memorandum_id',
        'person_involved_id'
    ];

    public function memorandum()
    {
        return $this->belongsTo(Memorandum::class)->with('memorandumtype');
    }

    public function personInvolved()
    {
        return $this->belongsTo(PersonInvolved::class)->with('person');
    }
}
