<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{

    public function scopeWithWhereHas($query, $relation, $constraint)
    {
        return $query->whereHas($relation, $constraint)
            ->with([$relation => $constraint]);
    }

    protected $fillable =
        [
            "id",
            "name",
            "short_name",
            "tin",
            "dv",
            "address",
            "code",
            "agreements",
            "category",
            "city",
            "country_code",
            "creation_date",
            "disabled",
            "email",
            "encoding_characters",
            "interface_id",
            "logo",
            "parent_id",
            "pbx",
            "regional_id",
            "send_email",
            "settings",
            "slogan",
            "phone",
            "email_contact",
            "social_reason",
            "document_type",
            "state",
            "telephone",
            "type",
            "api_key",
            "globo_id",
            "simbol",
            "payment_frequency",
            "account_type",
            "account_number",
            "bank_id",
            "payment_method",
            "base_salary",
            "paid_operator",
            "law_1429",
            "law_590",
            "law_1607",
            "transportation_assistance",
            "arl_id",
            "night_end_time",
            "night_start_time",
            "max_late_arrival",
            "max_holidays_legal",
            "max_extras_hours",
            "page_heading"
        ];

    public function bankAccounts()
    {
        return $this->hasMany(BankAccounts::class);
    }

    public function companyConfiguration()
    {
        return $this->hasOne(CompanyConfiguration::class);
    }

    public function groups()
    {
        return $this->hasMany(Group::class);
    }

    public function locations()
    {
        return $this->hasMany(Location::class);
    }

    public function fixedTurns()
    {
        return $this->hasMany(FixedTurn::class);
    }

    public function rotatingTurns()
    {
        return $this->hasMany(RotatingTurn::class);
    }

    public function arl()
    {
        return $this->belongsTo(Arl::class);
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    public function cityCompany()
    {
        return $this->belongsTo(Municipality::class, 'city');
    }

    public function responsibles()
    {
        return $this->hasMany(Responsible::class);
    }

    public function payConfiguration()
    {
        return $this->hasOne(PayConfigurationCompany::class);
    }
    public function Configuration()
    {
        return $this->hasOne(Configuration::class);
    }

    public function payrollSocialSecurityPerson()
    {
        return $this->hasMany(CompanyPayrollSocialSecurityPerson::class);
    }

    public function payrollOvertime()
    {
        return $this->hasMany(CompanyPayrollOvertime::class);
    }

    public function payrollRiskArl()
    {
        return $this->hasMany(CompanyPayrollRisksArl::class);
    }

    public function payrollParafiscal()
    {
        return $this->hasMany(CompanyPayrollParafiscal::class);
    }

    public function documentTypeInfo()
    {
        return $this->belongsTo(DocumentType::class, 'document_type');
    }
}
