<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class DiarioTurnoFijo extends Model
{
    protected $table = 'fixed_turn_diaries';

    protected $guarded = ['id'];

    protected $fillable = [
        'person_id',
        'date',
        'fixed_turn_id',
        'entry_time_one',
        'leave_time_one',
        'entry_time_two',
        'leave_time_two',
        'img_one',
        'img_two',
        'img_three',
        'img_four',
        'latitud',
        'longitud',
        'latitud_dos',
        'longitud_dos',
        'latitud_tres',
        'longitud_tres',
        'latitud_cuatro',
        'longitud_cuatro',
        'created_at',
        'updated_at',
        'temp_one',
        'temp_two',
        'temp_three',
        'temp_four',
    ];

    public function funcionario()
    {
        return $this->belongsTo(Person::class, 'person_id');
    }

    public function turnoFijo()
    {
        return $this->belongsTo(FixedTurn::class);
    }

    public function edit(): MorphMany
    {
        return $this->morphMany(DiaryEdit::class, 'diariable')->with('person');
    }

    public function scopeAlias($q, $alias)
    {
        return $q->from($q->getQuery()->from . " as " . $alias);
    }
}
