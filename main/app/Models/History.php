<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'icon',
        'type',
        'person_id',
        'user_id',
        'company_id',
        'historable_id',
        'historable_type',
    ];

    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    public function user()
    {
        return $this->belongsTo(Usuario::class)->select(['id', 'person_id'])->with('personImageName');
    }

    public function historable()
    {
        return $this->morphTo();
    }
}
