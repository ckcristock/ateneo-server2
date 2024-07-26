<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $table = 'countries';
    protected $fillable = [
        'name',
        'state',
        'iso',
        'dian_code',
        'code_phone',
    ];

    public function departments()
    {
        return $this->hasMany(Department::class)->orderBy('name')
            ->with([
                'municipalities' => function ($q) {
                    $q->select('*', 'name as text', 'id as value');
                }
            ]);
    }
}
