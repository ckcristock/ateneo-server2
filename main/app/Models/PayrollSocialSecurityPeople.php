<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollSocialSecurityPeople extends Model
{

    protected $table = "payroll_social_security_people";
    protected $fillable = [
        "prefix",
        "concept"
    ];
}
