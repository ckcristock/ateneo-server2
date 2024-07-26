<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationCertificate extends Model
{
    protected $table = 'application_certificate';

    protected $fillable = [
        "person_id",
        "patient_id",
        "cups_id",
        "date",
        "diagnostic_id",
        "fileActa",
        "observation"
    ];
}
