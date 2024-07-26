<?php

namespace App\Models;

use App\Http\Controllers\LateArrivalController;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Person extends Model
{
    protected $guarded = [''];
    protected $hidden = ['pivot'];

    protected $fillable = [
        'identifier',
        "type_document_id",
        'first_name',
        'second_name',
        'first_surname',
        'second_surname',
        'full_name',
        "birth_date",
        'blood_type',
        'phone',
        'email',
        'address',
        'title',
        'image',
        'image_blob',
        'signature_blob',
        'eps_id',
        'compensation_fund_id',
        'degree',
        'number_of_children',
        'people_type_id',
        'severance_fund_id',
        'shirt_size',
        'shue_size',
        'pension_fund_id',
        'arl_id',
        'personId',
        'persistedFaceId',
        'sex',
        'status',
        'pants_size',
        'signature',
        'color',
        "medical_record",
        "company_id",
        'specialities', //!PUEDE QUE NO SE USE
        "department_id",
        "municipality_id",
        'external_id',
        'date_last_session',
        'to_globo',
        'can_schedule',
        'cell_phone',
        'payroll_risks_arl_id',
        'company_worked_id',
        'dispensing_point_id',
        'place_of_birth',
        'gener',
        'visa',
        'passport_number',
        'marital_status',
        'folder_id',
    ];

    public function scopeAlias($q, $alias)
    {
        return $q->from($q->getQuery()->from . " as " . $alias);
    }

    public function specialities()
    {
        return $this->belongsToMany(Speciality::class)->select(['id']);
        // ->withPivot('id');
    }

    public function responsible()
    {
        return $this->hasOne(Responsible::class, 'person_id');
    }

    public function payrollRiskArl()
    {
        return $this->belongsTo(PayrollRisksArl::class, 'payroll_risks_arl_id');
    }

    public function specialties()
    {
        return $this->belongsToMany(Speciality::class);
    }

    public function usuario()
    {
        return $this->hasOne(Usuario::class, 'person_id', 'id');
    }

    public function locations()
    {
        return $this->belongsToMany(Location::class);
    }

    public function severance_fund()
    {
        return $this->belongsTo(SeveranceFund::class);
    }

    public function scopeCompleteName($q)
    {
        return $q->selectRaw("id, CONCAT_WS(' ', first_name, second_name, first_surname, second_surname) as complete_name");
    }

    public function peopleType()
    {
        return $this->belongsTo(PeopleType::class);
    }

    public function onePreliquidatedLog()
    {
        return $this->hasOne(PreliquidatedLog::class, 'person_id', 'id')
            ->withDefault(function ($person, $prelg) {
                $prelg->status = 'PreLiquidado';
            });
    }


    public function contractUltimateLiquidated()
    {
        return $this->hasOne(PreliquidatedLog::class)->with('workContractBT')->latest();
    }

    public function getFullNameAttribute()
    {
        return $this->attributes['first_name'] . ' ' . $this->attributes['first_surname'];
    }

    public function contractultimate()
    {
        return $this->hasOne(WorkContract::class)
            ->with('position.dependency.group', 'work_contract_type', 'contract_term')
            ->where('liquidated', 0)
            ->orderBy('id', 'DESC');
    }

    public function scopeActiveWithContractInCompany(Builder $query)
    {
        return $query->where('status', 'activo')
                     ->whereHas('contractultimate', function ($query) {
                         $query->where('company_id', getCompanyWorkedId());
                     });
    }

    public function scopeWithFullName(Builder $query)
    {
        return $query->select('id as Id_Cliente', DB::raw('CONCAT_WS(" ", first_name, first_surname) as Nombre'));
    }

    public function contractultimateFullInformation()
    {
        return $this->hasOne(WorkContract::class)
            ->with(
                'position.dependency.group',
                'work_contract_type',
                'contract_term',
                'company',
                'bonifications.ingreso',
                'fixedTurn',
                'rotatingTurn'
            )->where('liquidated', 0)->orderBy('id', 'DESC');
    }
    public function work_contract()
    {
        return $this->hasOne(WorkContract::class)->with('position', 'company');
    }

    public function work_contract_with_turn()
    {
        return $this->hasMany(WorkContract::class)->with('rotatingTurnWithDiaries');
    }

    public function work_contracts()
    {
        return $this->hasMany(WorkContract::class)->with('company', 'position.dependency.group', 'work_contract_type', 'contract_term');
    }

    public function liquidado()
    {
        return $this->hasOne(WorkContract::class);
        //->with('cargo.dependencia.centroCosto', 'tipo_contrato')->where('liquidado', 1);
    }

    public function payroll_factors()
    {
        return $this->hasMany(PayrollFactor::class);
    }

    /**
     * una persona tiene muchas llegadas tardes
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function lateArrivals()
    {
        return $this->hasMany(LateArrival::class);
    }

    public function diariosTurnoFijo()
    {
        return $this->hasMany(DiarioTurnoFijo::class);
    }

    public function diariosTurnoRotativo()
    {
        return $this->hasMany(DiarioTurnoRotativo::class, 'person_id', 'id');
        ;
    }
    public function diariosTurnoRotativoAyer()
    {
        return $this->hasMany(DiarioTurnoRotativo::class);
    }
    public function diariosTurnoRotativoHoy()
    {
        return $this->hasMany(DiarioTurnoRotativo::class);
    }

    public function turnoFijo()
    {
        return $this->belongsTo(FixedTurn::class);
    }

    public function horariosTurnoRotativo()
    {
        return $this->hasMany(RotatingTurnHour::class)->with('turnoRotativo');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function companyWorked()
    {
        return $this->belongsTo(Company::class, 'company_worked_id');
    }


    public function restriction()
    {
        return $this->hasMany(Restriction::class);
    }

    public function companies()
    {
        return $this->belongsToMany(Company::class);
    }

    public function documentType()
    {
        return $this->belongsTo(TypeDocument::class, 'type_document_id');
    }

    public function eps()
    {
        return $this->belongsTo(Eps::class);
    }

    public function arl()
    {
        return $this->belongsTo(Arl::class);
    }

    public function pension_funds()
    {
        return $this->belongsTo(PensionFund::class, 'pension_fund_id');
    }

    public function compensation_fund()
    {
        return $this->belongsTo(CompensationFund::class);
    }

    public function scopeFullName($q)
    {
        return $q->select('*', DB::raw("CONCAT_WS(' ', first_name, second_name, first_surname, second_surname) as full_names"));
    }

    public function scopeImageName($q)
    {
        return $q->select('id', 'image', DB::raw("CONCAT_WS(' ', first_name, second_name, first_surname, second_surname) as full_names"));
    }

    public function scopeName($q)
    {
        return $q->select('*', DB::raw('CONCAT_WS(" ", first_name, first_surname) as person'));
    }
    public function scopeOnlyName($q)
    {
        return $q->select('id', 'image', DB::raw('CONCAT_WS(" ", first_name, first_surname) as person'));
    }

    public function personPayrollPayment()
    {
        return $this->hasOne(PersonPayrollPayment::class, 'person_id', 'id')->latest();
    }

    public function personPayrollPayments()
    {
        return $this->hasMany(PersonPayrollPayment::class, 'person_id', 'id');
    }

    public function provisionPersonPayrollPayments()
    {
        return $this->hasMany(ProvisionsPersonPayrollPayment::class, 'person_id', 'id');
    }

    public function dispensingPoints()
    {
        return $this->belongsToMany(Dispensing::class, 'dispensing_point_person', 'person_id', 'dispensing_point_id');
    }

    public function dispensingPoint()
    {
        return $this->belongsTo(Dispensing::class, 'dispensing_point_id');
    }
}
