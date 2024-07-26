<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkCertificate extends Model
{
    protected $fillable = [
        'person_id',
        'file',
        'reason',
        'information',
        'addressee'
    ];
    protected $appends = ['downloading'];
    public function person()
    {
        return $this->belongsTo(Person::class)->fullName();
    }
    public function getDownloadingAttribute()
    {
        return false;
    }
}
