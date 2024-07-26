<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Responsible extends Model
{
    protected $fillable = ['name', 'person_id', 'company_id'];

    public function person()
    {
        return $this->belongsTo(Person::class)->fullName();
    }
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
