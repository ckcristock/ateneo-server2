<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class DiarioTurnoRotativo extends Model
{
    protected $table = 'rotating_turn_diaries';
    //protected $guarded = ['id'];
    protected $fillable = [
        'person_id',
        'date',
        'leave_date',
        'rotating_turn_id',
        'entry_time_one',
        'leave_time_one',
        'launch_one_date',
        'launch_two_date',
        'breack_one_date',
        'breack_two_date',
        'launch_time_one',
        'launch_time_two',
        'breack_time_one',
        'breack_time_two',
        'img_one',
        'img_two',
        'img_launch_one',
        'img_launch_two',
        'img_breack_one',
        'img_breack_two',
        'temp_one',
        'temp_two',
    ];
    protected $hidden = ['created_at', 'updated_at'];


    public function turnoRotativo()
    {
        return $this->belongsTo(RotatingTurn::class, 'rotating_turn_id');
        // return $this->belongsTo(RotatingTurn::class, 'turno_rotativo_id');
    }

    public function funcionario()
    {
        return $this->belongsTo(Person::class, 'person_id');
    }

    public function edit(): MorphMany
    {
        return $this->morphMany(DiaryEdit::class, 'diariable')->orderByDesc('id')->with('person');
    }

    public function scopeAlias($q, $alias)
    {
        return $q->from($q->getQuery()->from . " as " . $alias);
    }
}
