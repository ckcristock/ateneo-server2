<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lunch extends Model
{
    protected $fillable = [
        'value',
        'user_id',
        'person_id',
        'dependency_id',
        'state',
        'apply'
    ];

    public function lunchPerson()
    {
        return $this->hasMany(LunchPerson::class)->with([
            'person' => function ($q) {
                $q->select('id', 'first_name', 'second_name', 'first_surname', 'second_surname');
            }
        ]);
    }

    public function user()
    {
        return $this->belongsTo(User::class)->with([
            'person' => function ($q) {
                $q->select('id', 'first_name', 'second_name', 'first_surname', 'second_surname');
            }
        ]);
    }
}
