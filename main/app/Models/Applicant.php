<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Applicant extends Model
{

    protected $guarded = ['id'];
    protected $fillable = [
        'job_id',
        'name',
        'surname',
        'email',
        'phone',
        'city',
        'passport',
        'visa',
        'visaType_id',
        'education',
        'experience_year',
        'conveyance',
        'driving_license_id',
        'curriculum',
    ];

    public function visaType()
    {
        return $this->belongsTo(VisaType::class, 'visaType_id', 'id');
    }
    public function drivingLicense()
    {
        return $this->belongsTo(DrivingLicense::class);
    }
}
