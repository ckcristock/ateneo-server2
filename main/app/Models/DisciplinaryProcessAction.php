<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisciplinaryProcessAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'disciplinary_process_id',
        'user_id',
        'action_type_id',
        'description',
        'file',
        'date'
    ];

    public function user()
    {
        return $this->belongsTo(Usuario::class, 'user_id')->select(['id', 'person_id'])->with('personImageName');
    }

    public function actionType()
    {
        return $this->belongsTo(ActionType::class, 'action_type_id');
    }

}
