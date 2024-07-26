<?php

namespace App\Models;

use App\Http\Libs\Nomina\Calculos\CalculoExtra;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollOvertime extends Model
{

    protected static $funcionario;

    protected $guarded = ['id'];

    protected $fillable = [
        'prefix'
    ];

    public function accounts()
    {
        return $this->hasOne(CompanyPayrollOvertime::class)
            ->with([
                'accountPlan' => function ($query) {
                    $query->selectRaw('Id_Plan_Cuentas, Id_Plan_Cuentas as value, Codigo_Niif, Nombre_Niif as text');
                }
            ])
            ->where('company_id', Person::find(Auth()->user()->person_id)->company_worked_id);
    }

    public function cuentaContable()
    {
        return $this->belongsTo(AccountPlan::class, 'account_plan_id', 'Codigo_Niif');
    }

    public function scopePrefijo($query, $indice)
    {
        $payrollOvertimeId = $query->where('prefix', $indice)->first()->id;
        $percentage = CompanyPayrollOvertime::where([
            ['payroll_overtime_id', $payrollOvertimeId],
            ['company_id', Person::find(Auth()->user()->person_id)->company_worked_id],
        ])->first();
        if (!$percentage) {
            return 0;
        }
        return $percentage->percentage;
    }

    public static function enviarPorcentajes($indices = [])
    {
        $porcentajes = [];
        foreach ($indices as $indice) {

            $porcentajes[$indice] = self::prefijo($indice);
        }

        return $porcentajes;
    }
    /**
     * Settea la propiedad funcionario filtrando al funcionario que se pase por el parámetro $id,
     * retorna una nueva instancia de la clase
     */
    public static function extrasFuncionarioWithPerson($persona)
    {
        //dd(gettype($persona));
        if (gettype($persona) === 'string' || gettype($persona) === 'integer') {
            self::$funcionario = Person::with('contractultimate')->find($persona);
        } else {
            self::$funcionario = $persona;
        }

        //dd(self::$funcionario);

        return new self;
    }

    /**
     * Obtiene los prefijos de la tabla nomina_horas_extras, filtra los reportes de horas extras que tenga
     * el funcionario entre el parámetro fecha de inicio y el de fecha de fin , obtiene su salario base
     * y realiza el cálculo correspondiente con esos tres valores (Ver la clase Cálculo extras)
     *
     * @param integer $fechaInicio
     * @param integer $fechaFin
     * @return \Illuminate\Support\Collection
     */


    public function fromTo($fechaInicio, $fechaFin)
    {
        //consulta los prefijos en la DB PayrollOvertime
        $prefijos = PayrollOvertime::get(['prefix'])->keyBy('prefix')->keys();
        //devuelve un builder
        $reporteExtras = ExtraHourReport::where('person_id', self::$funcionario->id)->whereBetween('date', [$fechaInicio, $fechaFin]);

        //revisar que no vuelva a consultar la persona
        //$salarioPartial = Person::with('contractultimate')->where('id', self::$funcionario->id)->firstOrFail();
        //dd(self::$funcionario->contractultimate->salary);
        //si una perona no tiene contrato, podría haber un error aquí o si no recibió el objeto completo
        $salario = self::$funcionario->contractultimate->salary;
        $calculoExtras = new CalculoExtra($prefijos, $reporteExtras, $salario);
        $calculoExtras->calcularCantidadHoras();
        $calculoExtras->setHorasReportadas($calculoExtras->getCantidadHoras());
        $calculoExtras->setPorcentajes(
            PayrollOvertime::enviarPorcentajes($calculoExtras->getPrefijos())
        );
        $calculoExtras->calcularTotalHoras();
        $calculoExtras->calcularValorTotalHoras();
        return $calculoExtras->crearColeccion();
    }
}
