<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisciplinaryClosure extends Model
{
    use HasFactory;

    protected $fillable = [
        'disciplinary_closure_reasons_id',
        'disciplinary_process_id',
        'user_id',
        'description',
        'file',
    ];

    public function user()
    {
        return $this->belongsTo(Usuario::class, 'user_id')->select('id', 'person_id')->with('personImageName');
    }

    public function closureReason()
    {
        return $this->belongsTo(DisciplinaryClosureReason::class, 'disciplinary_closure_reasons_id');
    }

    public function process()
    {
        return $this->belongsTo(DisciplinaryProcess::class, 'disciplinary_process_id');
    }
}
