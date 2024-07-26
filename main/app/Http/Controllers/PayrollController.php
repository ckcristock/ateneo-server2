<?php

namespace App\Http\Controllers;

ini_set('max_execution_time', 180); //3 minutes

use App\Models\ElectronicPayroll;
use App\Http\Libs\Nomina\Facades\NominaDeducciones;
use App\Http\Libs\Nomina\Facades\NominaIngresos;
use App\Http\Libs\Nomina\Facades\NominaNovedades;
use App\Http\Libs\Nomina\Facades\NominaPago;
use App\Http\Libs\Nomina\Facades\NominaProvisiones;
use App\Http\Libs\Nomina\Facades\NominaRetenciones;
use App\Http\Libs\Nomina\Facades\NominaSalario;
use App\Http\Libs\Nomina\Facades\NominaSeguridad;
use App\Models\Company;
use App\Models\Configuration;
use App\Models\PayrollOvertime;
use App\Models\PayrollPayment;
use App\Models\PayrollSocialSecurityPerson;
use App\Models\Person;
use App\Models\PersonPayrollPayment;
use App\Services\PayrollReport;
use App\Services\PayrollService;
use App\Traits\ApiResponser;
use App\Traits\ElectronicDian;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Exports\NominaExport;
use App\Exports\NovedadesExport;
use App\Models\ComprobanteConsecutivo;
use App\Models\PayrollFactor;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Responsible;
use stdClass;


class PayrollController extends Controller
{
    use ApiResponser;
    use ElectronicDian;

    private function getCompany()
    {
        return Person::find(Auth()->user()->person_id)->company_worked_id;
    }

    public function reportDian($id, $idPersonPayroll = null)
    {
        $payroll = PayrollPayment::with('company')
            ->with('company.configuration')
            ->with('company.payConfiguration')
            ->where('id', $id)->first();
        $poplePay = PersonPayrollPayment::where('payroll_payment_id', $id)
            ->whereNull('electronic_reported')
            ->when($idPersonPayroll, function ($q, $fill) {
                $q->where('id', $fill);
                ;
            })
            ->get();
        $poplePay->each(function ($personPay) use ($payroll) {
            $config = Configuration::where('company_id', $payroll->company->id)->first();
            $configData = $config->consecutivoLevel1('Nomina', 'Nomina');
            $data = [];
            $data['type_document_id'] = 7;
            $data['resolution_number'] = 2;
            $data['resolution_number'] = 7;
            $data['date_pay'] = date("Y-m-d", strtotime($payroll->created_at));
            $data['payroll_period'] = $payroll->payment_frequency;
            $data['date'] = date("Y-m-d", strtotime($payroll->created_at));
            $data['observation'] = 'nomina electronica';
            $data['prefix'] = $configData->prefix;
            $data['number'] = $configData->number;
            $data['code'] = $configData->code;
            $data['file'] = $configData->code;
            $data['date_start_period'] = $payroll->start_period;
            $data['date_end_period'] = $payroll->end_period;
            $data['hour'] = date("H:i:s", strtotime($payroll->created_at)) . '-05:00';
            $person = [];
            $workContract = $personPay->person->contractultimate;
            if (!$workContract->reported_integraition_dian) {
                $data['integration_date'] = date("Y-m-d", strtotime($workContract->date_of_admission));
                $person['contractultimate'] = 1;
                /* TODO activar */
                //$workContract->contractultimate->save();
            }
            $person['historic_worked_time'] = PayrollReport::calculateWorkedDays($workContract->date_of_admission, $payroll->end_period);
            $person['salary'] = $workContract->salary;
            $person['code'] = $personPay->person->identifier;
            /* TODO salario integral quemado */
            $person['salary_integral'] = "true";
            $person['worker_type'] = [];
            $person['worker_type']['code'] = $workContract->worker_type_dian_code;

            /* TODO salario subtipo de salario */
            $person['worker_subtype'] = [];
            $person['worker_subtype']['code'] = "00";

            $person['work_contract_type'] = [];
            /*   $tipoContrato =  $workContract->work_contract_type;*/
            $person['work_contract_type']['code'] = $workContract->work_contract_type->dian_code;



            $person['high_risk_pension'] = "false";
            $person['identifier'] = $personPay->person->identifier;
            $person['first_name'] = $personPay->person->first_name;
            $person['middle_name'] = $personPay->person->second_name;
            $person['last_name'] = $personPay->person->first_surname;
            $person['last_names'] = $personPay->person->second_surname;

            $person['type_document_identification'] = [];

            $person['type_document_identification']['code'] = $personPay->person->documentType->dian_code;

            $person['work_place'] = [];
            $person['work_place']['country'] = [];
            $person['work_place']['country']['code'] = "CO";

            /*   dd($person); */

            /* TODO monicipio quemado */
            $person['work_place']['municipality'] = [];

            $person['work_place']['municipality']['department'] = [];

            $person['work_place']['country']['code'] = "CO";

            $person['work_place']['municipality']['code'] = "05001";
            $person['work_place']['municipality']['department']['code'] = "05";

            $person['work_place']['addres'] = $personPay->person->address;

            $data['person'] = $person;

            $pay = [];
            $pay['payroll_pay_formate'] = [];
            $pay['payroll_pay_formate']['code'] = 1;

            $pay['payroll_pay_method'] = [];
            $pay['payroll_pay_method']['code'] = 1;

            $data['pay'] = $pay;

            $accrued = [];
            $accrued['basic'] = [];
            $accrued['basic']['worked_days'] = $personPay->worked_days;
            $accrued['basic']['salary_payroll'] = $personPay->net_salary;


            if ($payroll->company->payConfiguration->pay_transportation_assistance) {

                $accrued['transport_subsidy'] = [];
                $accrued['transport_subsidy']['salarial'] = $payroll->company->transportation_assistance;
            }

            /* Incapacidades */

            $factors = DB::table('payroll_factors as p')
                ->join('disability_leaves as d', 'd.id', 'p.disability_leave_id')
                ->where('person_id', $personPay->person->id)
                ->whereDate('p.date_start', '>=', DB::raw("date('$payroll->start_period')"))
                ->whereDate('p.date_end', '<=', DB::raw("date('$payroll->end_period')"))
                ->select('p.*', 'd.code_dian', 'd.maternity', 'd.not_paid_license', 'd.paid_license')
                ->get();



            $inhabilities = [];
            $licencess = [];
            $licencess['mp'] = [];
            $licencess['r'] = [];
            $licencess['nr'] = [];

            $factors->each(function ($x) use ($accrued, &$inhabilities, &$licencess, $personPay) {

                $data = [];
                $data['date_start'] = Carbon::parse($x->date_start)->format('Y-m-d');
                $data['date_end'] = Carbon::parse($x->date_end)->format('Y-m-d');
                $data['days'] = ($x->number_days + 1);
                $data['type'] = $x->code_dian;
                $data['value'] = ($personPay->person->contractultimate->salary * ($x->number_days + 1)) / 30;


                if ($x->disability_type == 'Incapacidad') {
                    $inhabilities[] = $data;
                }
                if ($x->disability_type == 'Licencia' && $x->maternity == 1) {
                    $licencess['mp'][] = $data;
                }
                if ($x->disability_type == 'Licencia' && $x->not_paid_license == 1) {
                    $licencess['nr'][] = $data;
                    unset($data['value']);
                }
                if ($x->disability_type == 'Licencia' && $x->paid_license == 1) {
                    $licencess['r'][] = $data;
                }
            });


            /*         count($licencess->mp) == 0 ?  unset($licencess->mp) : ''; */

            $accrued['inability'] = $inhabilities;
            $accrued['licences'] = $licencess;

            $bonus = [];
            $assistances = [];
            $others = [];
            $commissions = [];

            $countableNotIncomes = DB::table('benefit_not_incomes as n')
                ->join('countable_income as c', 'c.id', 'n.countable_income_id')
                ->where('person_id', $personPay->person->id)
                ->whereDate('n.created_at', '>=', DB::raw("date('$payroll->start_period')"))
                ->whereDate('n.created_at', '<=', DB::raw("date('$payroll->end_period')"))
                ->select('n.*', 'c.type', 'c.bonus', 'c.assistence', 'c.others', 'c.commission')
                ->get();

            $countableIncomes = DB::table('benefit_incomes as n')
                ->join('countable_income as c', 'c.id', 'n.countable_income_id')
                ->where('person_id', $personPay->person->id)
                ->whereDate('n.created_at', '>=', DB::raw("date('$payroll->start_period')"))
                ->whereDate('n.created_at', '<=', DB::raw("date('$payroll->end_period')"))
                ->select('n.*', 'c.type', 'c.bonus', 'c.assistence', 'c.others', 'c.commission')
                ->get();


            //dd($countableIncomes);

            $countableNotIncomes->each(function ($x) use (&$bonus, &$assistances, &$others, &$commissions) {
                if ($x->bonus == 1) {
                    $bonus[] = ['no_salarial' => $x->value];
                }
                if ($x->assistence == 1) {
                    $assistances[] = ['no_salarial' => $x->value];
                }

                if ($x->others == 1) {
                    $others[] = ['no_salarial' => $x->value, 'description' => 'Otra deducción'];
                }
                if ($x->commission == 1) {
                    $commissions[] = ['value' => $x->value];
                }
            });

            $countableIncomes->each(function ($x) use (&$bonus, &$assistances, &$others, &$commissions) {
                if ($x->bonus == 1) {
                    $bonus[] = ['salarial' => $x->value];
                }

                if ($x->assistence == 1) {
                    $assistances[] = ['salarial' => $x->value];
                }
                if ($x->others == 1) {
                    $others[] = ['salarial' => $x->value, 'description' => 'Otra deducción'];
                }
                if ($x->commission == 1) {
                    $commissions[] = ['value' => $x->value];
                }
            });

            $accrued['inability'] = $inhabilities;
            $accrued['licences'] = $licencess;

            $bonus ? $accrued['bonus'] = $bonus : null;
            $assistances ? $accrued['assistances'] = $assistances : null;
            $others ? $accrued['others'] = $others : null;
            $commissions ? $accrued['commissions'] = $commissions : null;

            $data['accrued'] = $accrued;



            /* deductions */

            $deductions = [];

            $deductions['healt'] = [];
            $deductions['pension_funds'] = [];

            $retDed = $this->getRetenciones($personPay->person->id, $payroll->start_period, $payroll->end_period);




            $deductions['healt']['deduction'] = $retDed['total_retenciones']['Salud'];
            $deductions['healt']['percentage'] = $retDed['porcentajes']['Salud'] * 100;

            $deductions['pension_funds']['deduction'] = $retDed['total_retenciones']['Pensión'];
            $deductions['pension_funds']['percentage'] = $retDed['porcentajes']['Pensión'] * 100; /**/


            $deductionsDB = DB::table('deductions as n')
                ->join('countable_deductions as cd', 'cd.id', 'n.countable_deduction_id')
                ->where('n.person_id', $personPay->person->id)
                ->whereDate('n.created_at', '>=', DB::raw("date('$payroll->start_period')"))
                ->whereDate('n.created_at', '<=', DB::raw("date('$payroll->end_period')"))
                ->select('n.value', 'cd.loan', 'cd.others')
                ->get();


            /*  $loan
             */
            $loans = [];
            $other_deductions = [];
            $deductionsDB->each(function ($x) use (&$loans, &$other_deductions) {
                if ($x->loan == 1) {
                    $x->description = "prestamo cuota";
                    $loans[] = $x;
                }
                if ($x->others == 1) {
                    $other_deductions[] = $x;
                }
            });

            $deductions['loans'] = $loans;
            $deductions['other_deductions'] = $other_deductions;

            $data['deductions'] = $deductions;

            /*  */

            $novedadesTotal = $this->getNovedades($personPay->person->id, $payroll->start_period, $payroll->end_period)['valor_total'];
            $ingresosAdicionalesTotal = $this->getIngresos($personPay->person->id, $payroll->start_period, $payroll->end_period)['valor_total'];

            $extrasTotal = $this->getExtrasTotales($personPay->person->id, $payroll->start_period, $payroll->end_period)['valor_total'];

            $deduccionesTotal = $this->getDeducciones($personPay->person->id, $payroll->start_period, $payroll->end_period)['valor_total'];
            $retencionTotal = $this->getRetenciones($personPay->person->id, $payroll->start_period, $payroll->end_period)['valor_total'];

            $salario = $this->getPagoNeto($personPay->person->id, $payroll->start_period, $payroll->end_period)['total_valor_neto'];


            $totals = [];

            $totals['accrued'] = number_format($salario + ($deduccionesTotal + $retencionTotal), 2, '.', '');

            $totals['deductions'] = number_format($deduccionesTotal + $retencionTotal, 2, '.', '');

            $totals['voucher'] = number_format($salario, 2, '.', '');


            $data['totals'] = $totals;


            $data['cune_propio'] = $this->cuneGenerate($data, $payroll->company, '00777', '102', 1);

            $responseDian = $this->sendElectronicPayroll($data, $payroll);

            $payrollElectronic = new ElectronicPayroll();
            $payrollElectronic->status = $responseDian['status'];
            $payrollElectronic->message = $responseDian['message'];
            $payrollElectronic->errors = $responseDian['errors'];
            $payrollElectronic->person_payroll_payment_id = $personPay->id;
            $payrollElectronic->cune = $responseDian['status'] == 'succeded' ? $data['cune_propio'] : '';
            $payrollElectronic->code = $responseDian['status'] == 'succeded' ? $data['code'] : '';
            $payrollElectronic->save();

            $personPay->status = $responseDian['status'];


            if ($responseDian['status'] == 'succeded') {
                $personPay->code = $payrollElectronic->code;
                $personPay->cune = $payrollElectronic->cune;

                $personPay->electronic_reported = 1;
                $personPay->electronic_reported_date = $data['date'];
                $personPay->user_electronic_reported = auth()->user()->id;
                $config->savePrefix('Nomina');
            }

            $personPay->save();



            /* -------- */
            /* dd($deductions,$); */
        });

        return $this->success('Reportado correctamente');
    }

    /*  public function store(PagosNominaStoreRequest $pagosNominaStoreRequest) */
    public function store()
    {
        try {
            $atributos = request()->all();
            $atributos['start_period'] = Carbon::parse(request()->get('start_period'))->format('Y-m-d');
            $atributos['end_period'] = Carbon::parse(request()->get('end_period'))->format('Y-m-d');
            $atributos['payment_frequency'] = Company::find($this->getCompany())['payment_frequency'];
            $companyId = Company::find($this->getCompany())->id;
            $atributos['company_id'] = $companyId;
            $funcionarios = Person::with('contractultimate')
                ->whereHas('contractultimate', function ($query) use ($atributos, $companyId) {
                    $query->where(function ($subquery) use ($atributos) {
                        $subquery->whereDate('date_of_admission', '<=', $atributos['end_period'])
                            ->whereDate('date_end', '>=', $atributos['start_period'])
                            ->orWhereNull('date_end');
                    })
                        ->where(function ($subquery) use ($companyId) {
                            $subquery->where('company_id', $companyId)
                                ->where('liquidated', '0');
                        });
                })
                ->where('status', '!=', 'Liquidado')
                /* ->where('company_id', $atributos['company_id']) */
                ->get();
            $atributos['code'] = generateConsecutive('payroll_payments');

            $pagoNomina = PayrollPayment::create($atributos);
            sumConsecutive('payroll_payments');

            $funcionarios->each(function ($funcionario) use ($pagoNomina) {
                $code_person = generateConsecutive('person_payroll_payments');
                $salario = $this->getSalario($funcionario, $pagoNomina->start_period, $pagoNomina->end_period);
                $pagoNomina->personPayrollPayment()->create([
                    'person_id' => $funcionario->id,
                    'payroll_payment_id' => $pagoNomina->id,
                    'worked_days' => $salario['worked_days'],
                    'salary' => $salario['salary'],
                    'code' => $code_person,
                    'transportation_assistance' => $salario['transportation_assistance'],
                    'retentions_deductions' => $this->getRetenciones($funcionario, $pagoNomina->start_period, $pagoNomina->end_period)['valor_total'],
                    'net_salary' => $this->getPagoNeto($funcionario, $pagoNomina->start_period, $pagoNomina->end_period)['total_valor_neto'],
                ]);

                $previsiones = $this->getProvisiones($funcionario, $pagoNomina->start_period, $pagoNomina->end_period);


                $pagoNomina->provisionsPersonPayrollPayment()->create([
                    'person_id' => $funcionario->id,
                    'payroll_payment_id' => $pagoNomina->id,
                    'severance' => $previsiones['resumen']['cesantias']['valor'],
                    'severance_interest' => $previsiones['resumen']['intereses_cesantias']['valor'],
                    'prima' => $previsiones['resumen']['prima']['valor'],
                    'vacations' => $previsiones['resumen']['vacaciones']['valor'],
                    'accumulated_vacations' => $previsiones['dias_vacaciones']['vacaciones_acumuladas_periodo'],
                    'total_provisions' => $previsiones['valor_total']
                ]);

                $seguridad = $this->getSeguridad($funcionario, $pagoNomina->start_period, $pagoNomina->end_period);

                $pagoNomina->socialSecurityPersonPayrollPayment()->create([
                    'person_id' => $funcionario->id,
                    'payroll_payment_id' => $pagoNomina->id,
                    'health' => $seguridad['seguridad_social']['Salud'],
                    'pension' => $seguridad['seguridad_social']['Pensión'],
                    'risks' => $seguridad['seguridad_social']['Riesgos'],
                    'sena' => $seguridad['parafiscales']['Sena'],
                    'icbf' => $seguridad['parafiscales']['Icbf'],
                    'compensation_founds' => $seguridad['parafiscales']['Caja de compensación'],
                    'total_social_security' => $seguridad['valor_total_seguridad'],
                    'total_parafiscals' => $seguridad['valor_total_parafiscales'],
                    'total_social_security_parafiscals' => $seguridad['valor_total'],
                ]);
                sumConsecutive('person_payroll_payments');
            });

            return $this->success('Nómina guardada correctamente', $pagoNomina);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->errorResponse($th->getMessage(), 500);
        }
    }

    public function getFuncionario($identidad)
    {
        $funcionario = Person::where('id', '=', $identidad)->with('contractultimate')->first();
        if (!$funcionario) {
            return response()->json(['message' => 'Funcionario no encontrado'], 404);
        }

        return $funcionario;
    }

    function nextMonths()
    {
        try {
            return $this->success(PayrollService::getQuincena());
        } catch (\Throwable $th) {
            return $this->error($th->getMessage() . $th->getLine(), 402);
        }
    }

    public function downloadExcelNovedades($inicio, $fin)
    {
        try {
            $novedad = PayrollFactor::alias('pf')
                ->select(
                    'p.id',
                    'p.first_name',
                    'p.first_surname',
                    'p.identifier',
                    'pf.created_at',
                    'disability_leaves.concept',
                    'pf.observation',
                    'pf.number_days',
                    'pf.date_start',
                    'pf.date_end',
                )
                ->when($fin, function ($q) use ($inicio, $fin) {
                    $q->where('date_start', '>=', $inicio)
                        ->where('date_end', '<=', $fin);
                })
                ->when($inicio, function ($q) use ($inicio, $fin) {
                    $q->where('date_start', '>=', $inicio)
                        ->where('date_end', '<=', $fin);
                })
                ->join('people as p', 'pf.person_id', '=', 'p.id')
                ->join('disability_leaves', 'pf.disability_leave_id', '=', 'disability_leaves.id')
                ->get();

            $f_inicio = Carbon::create($inicio)
                ->format('d/m/Y');
            $f_fin = Carbon::create($fin)
                ->format('d/m/Y');
            foreach ($novedad as $nov) {
                $ini = explode(' ', $nov->date_start)[0];
                $fin = explode(' ', $nov->date_end)[0];
                $nov->date_start = Carbon::create($ini)->format('d-m-Y');
                $nov->date_end = Carbon::create($fin)->format('d-m-Y');
            }
            ;

            $novedades = [
                'novedades' => $novedad,
                'ini_date' => $f_inicio,
                'end_date' => $f_fin
            ];

            return Excel::download(new NovedadesExport($novedades), 'novedades.xlsx');
        } catch (\Throwable $th) {
            return $this->error(['Line: ' . $th->getLine() . ' -- File: ' . $th->getFile() . ' -- Message: ' . $th->getMessage()], 204);
        }
    }

    public function downloadExcelNomina(Request $request)
    {
        try {
            $datos = $request->all();
            foreach ($datos['funcionarios'] as $index => $funci) {
                $libranzaOsanciones = 0;
                $prestamos = 0;
                if (is_array($funci['deducciones']['deducciones'])) {
                    foreach ($funci['deducciones']['deducciones'] as $key => $value) {
                        if ($key != 'Prestamo') {
                            $libranzaOsanciones += $value;
                        } else {
                            $prestamos += $value;
                        }
                    }
                    $datos['funcionarios'][$index]['libranzas'] = $libranzaOsanciones;
                    $datos['funcionarios'][$index]['prestamos'] = $prestamos;
                }

                $diasLicencia = 0;
                $totalLicencia = 0;
                if (is_array($funci['novedades']['novedades'])) {
                    foreach ($funci['novedades']['novedades'] as $claveNovDi => $valorNovDi) {
                        if (explode(' ', $claveNovDi)[0] == 'Licencia') {
                            $diasLicencia += $valorNovDi;
                        }
                    }
                    $datos['funcionarios'][$index]['dias_licencia'] = $diasLicencia;
                    foreach ($funci['novedades']['novedades_totales'] as $claveTot => $valorTot) {
                        if (explode(' ', $claveTot)[0] == 'Licencia') {
                            $totalLicencia += $valorTot;
                        }
                    }
                    $datos['funcionarios'][$index]['total_licencia'] = $totalLicencia;
                }
            }
            return Excel::download(new NominaExport($datos), 'nomina.xlsx');
        } catch (\Throwable $th) {
            return $this->error(['Line: ' . $th->getLine() . ' -- File: ' . $th->getFile() . ' -- Message: ' . $th->getMessage()], 204);
        }
    }

    private function getResponsableRhName()
    {
        $companyWorkedId = $this->getCompany();
        $responsableRh = Responsible::where('company_id', $companyWorkedId)
            ->where('name', 'LIKE', '%DIRECTOR ADMINISTRATIVO Y FINANCIERO%')
            ->first();
        if ($responsableRh) {
            $person = Person::find($responsableRh->person_id);
            if ($person) {
                return $person->full_name;
            }
        }
        return 'Responsable de RRHH no encontrado';
    }

    public function getPdfsNomina(Request $request)
    {
        try {
            $data = $request->all();

            $empresa = Company::find($this->getCompany());
            $consecutivos = ComprobanteConsecutivo::where('Prefijo', 'NOM')
                ->orWhere('Prefijo', 'NDI')
                ->get();

            $consecNomina = '';
            $consecNominaIndiv = '';
            foreach ($consecutivos as $consec) {
                switch ($consec['Prefijo']) {
                    case 'NOM':
                        $consecNomina = $consec;
                        break;
                    case 'NDI':
                        $consecNominaIndiv = $consec;
                        break;
                }
            }

            $datosCabecera = new stdClass();
            $datosCabecera->Titulo = 'Colilla de pago';
            $datosCabecera->Codigo = $data['code'];
            $datosCabecera->Fecha = Carbon::create($data['inicio_periodo'])->toFormattedDateString() . ' al ' . Carbon::create($data['fin_periodo'])->toFormattedDateString();
            $datosCabecera->CodigoFormato = $consecNomina['format_code'];

            $company = new stdClass();
            $company->logo = $empresa->logo;
            $company->name = $empresa->social_reason;
            $company->document_number = $empresa->document_number;
            $company->verification_digit = $empresa->social_reason;



            $responsableRhName = $this->getResponsableRhName();

            $pdf = PDF::loadView('pdf.nomina_list', [
                'info' => $data['funcionarios'],
                'data' => $request->all(),
                'image' => '',
                'viewImage' => false,
                'datosCabecera' => $datosCabecera,
                'company' => $company,
                'consecIndividual' => $consecNominaIndiv,
                'responsableRhName' => $responsableRhName,
            ])
                ->setPaper([0, 0, 614.295, 397.485]);
            //dd($pdf);
            //$usuarios = $data['funcionarios'];

            /* return view('pdf.nomina_list',[
                        'data'=> $request->all(),
                        'info'=>$usuarios,
                        'image' =>'',
                        'datosCabecera'=> $datosCabecera,
                        'company'=>$company]); */
            return $pdf->stream('colilla_nomina.pdf');
        } catch (\Throwable $th) {
            return $this->error([
                'status' => 'error',
                'message' => 'Ocurrió un error',
                'data' => 'Msg: ' . $th->getMessage() . ' - line: ' . $th->getLine() . ' - File: ' . $th->getFile()
            ], 204);
        }
    }


    public function getPayrollPay($inicio = null, $fin = null, $return_type = 'success')
    {
        $current = !$inicio ? true : false;
        $frecuenciaPago = Company::find($this->getCompany())['payment_frequency'];
        $companyId = Company::find($this->getCompany())->id;
        $pagoNomina = $nomina = $paga = $idNominaExistente = null;
        $fechaInicioPeriodo = Carbon::now()->startOfMonth()->format("Y-m-d H:i:s");
        $fechaFinPeriodo = Carbon::now()->endOfMonth()->format("Y-m-d H:i:s");
        $totalSalarios = 0;
        $totalRetenciones = 0;
        $totalSeguridadSocial = 0;
        $totalParafiscales = 0;
        $totalProvisiones = 0;
        $totalExtras = 0;
        $totalIngresos = 0;
        $totalCostoEmpresa = 0;
        if ($frecuenciaPago === 'Quincenal') {
            if (date("Y-m-d") > date("Y-m-15 00:00:00")) {
                $fechaInicioPeriodo = date("Y-m-16 00:00:00");
            } else {
                $fechaFinPeriodo = date("Y-m-15 23:59:59");
            }
        }
        $fechaInicioPeriodo = $fechaInicioPeriodo;
        $fechaFinPeriodo = $fechaFinPeriodo;


        /**
         * Comprobar si los parámetros inicio y fin no son nulos, si no lo son, entonces significa
         * que se requiere ver o actualizar una nómina antigua ya que estos valores solo son asignados
         * desde el componente de historial de pagos en Vue cuando se realiza una petición.
         */
        if ($inicio) {
            $fechaInicioPeriodo = $inicio;
        }

        if ($fin) {
            $fechaFinPeriodo = $fin;
        }


        /**
         * Comprobar si ya existe un pago de nómina en el periodo
         */

        if ($current) {
            # code...
            $nomina = PayrollPayment::where('company_id', $companyId)->latest()->first();
        } else {
            $nomina = PayrollPayment::whereDate('start_period', $fechaInicioPeriodo)
                ->where('company_id', $companyId)
                ->whereDate('end_period', $fechaFinPeriodo)
                ->first();
        }
        if ($nomina) {
            $idNominaExistente = $nomina->id;
            $paga = $current ? Carbon::now()->between($nomina->start_period, $nomina->end_period) : true;
        }


        $fechasNovedades = function ($query) use ($fechaInicioPeriodo, $fechaFinPeriodo) {
            return $query->whereBetween('date_start', [$fechaInicioPeriodo, $fechaFinPeriodo])
                ->whereBetween('date_end', [$fechaInicioPeriodo, $fechaFinPeriodo])
                ->with('disability_leave');
        };

        $funcionarios = Person::with('personPayrollPayment')
            ->with(['payroll_factors' => $fechasNovedades])
            ->with('contractultimate')
            ->whereHas('contractultimate', function ($query) use ($fechaInicioPeriodo, $fechaFinPeriodo, $companyId) {
                $query->where(function ($subquery) use ($fechaInicioPeriodo, $fechaFinPeriodo) {
                    $subquery->whereDate('date_of_admission', '<=', $fechaFinPeriodo)
                        ->whereDate('date_end', '>=', $fechaInicioPeriodo)
                        ->orWhereNull('date_end');
                })
                    ->where(function ($subquery) use ($companyId) {
                        $subquery->where('company_id', $companyId)
                            ->where('liquidated', '0');
                    });
            })
            ->get();
        try {
            $funcionariosResponse = [];
            foreach ($funcionarios as $funcionario1) {
                $funcionario = Person::find($funcionario1->id);
                //cálculos que no dependen de otro
                $temIngresos = $this->getIngresos($funcionario, $fechaInicioPeriodo, $fechaFinPeriodo);
                $tempNovedades = $this->getNovedades($funcionario, $fechaInicioPeriodo, $fechaFinPeriodo);
                $tempDeducciones = $this->getDeducciones($funcionario, $fechaInicioPeriodo, $fechaFinPeriodo);
                $salarioBase = $this->getSalario($funcionario, $fechaInicioPeriodo, $fechaFinPeriodo);
                $tempExtras = $this->getExtrasTotales($funcionario, $fechaInicioPeriodo, $fechaFinPeriodo);
                $extras = $tempExtras["valor_total"];
                /** */
                $retencion = $this->getRetenciones(
                    $funcionario,
                    $fechaInicioPeriodo,
                    $fechaFinPeriodo,
                    $salarioBase,
                    $tempExtras,
                    $tempNovedades,
                    $temIngresos
                );

                $tempSeguridad = $this->getSeguridadWithParams(
                    $funcionario,
                    $fechaInicioPeriodo,
                    $fechaFinPeriodo,
                    $retencion,
                    $tempNovedades
                );
                $seguridad = $tempSeguridad['valor_total_seguridad'];
                $parafiscal = $tempSeguridad['valor_total_parafiscales'];

                $provision = $this->getProvisionesWithParams(
                    $funcionario,
                    $fechaInicioPeriodo,
                    $fechaFinPeriodo,
                    $salarioBase,
                    $tempExtras,
                    $tempNovedades,
                    $retencion
                );

                /***Lo siguiente evita la funcion getPagoNeto para evitar
                 * que use muchos de los facades Nominas ya empleados anteriormente
                 **/
                //$salario = $this->getPagoNeto($funcionario, $fechaInicioPeriodo, $fechaFinPeriodo)['total_valor_neto'];

                /* $code = PersonPayrollPayment::where('person_id', $funcionario->id)->select('code')->latest()->first(); */
                //Nos ahorramos esta consulta poniendo una relación en Personas
                $code = $funcionario1->personPayrollPayment->code ?? '';
                //$previsiones = $this->getProvisiones($funcionario, $fechaInicioPeriodo, $fechaFinPeriodo);

                $salario = $this->getPagoNetoWithParams(
                    $funcionario,
                    $salarioBase,
                    $tempExtras,
                    $tempNovedades,
                    $temIngresos,
                    $retencion,
                    $tempDeducciones,
                    $fechaInicioPeriodo,
                    $fechaFinPeriodo
                );

                //que no incluya horas extras ni ingresos adicionales
                //$totalSalarios +=  $salario['total_valor_neto'];
                $totalSalarios += $salarioBase['salary'];
                $totalRetenciones += $retencion['valor_total'];
                $totalSeguridadSocial += $seguridad;
                $totalParafiscales += $parafiscal;
                $totalProvisiones += $provision["valor_total"];
                $totalExtras += $extras;
                $totalIngresos += $temIngresos["valor_total"];
                //dd($fechaInicioPeriodo);

                $funcionariosResponse[] = [
                    'id' => $funcionario->id,
                    'identifier' => $funcionario->identifier,
                    'name' => $funcionario->first_name,
                    'surname' => $funcionario->first_surname,
                    'image' => $funcionario->image,
                    'salario_neto' => $salario['total_valor_neto'],
                    'Salario_nomina' => $funcionario1->personPayrollPayment->net_salary ?? '',
                    'city' => $funcionario->place_of_birth,
                    'basic_salary' => $funcionario->contractultimate->salary,
                    'position' => $funcionario->contractultimate->position->name,
                    'code' => $code,
                    'date_of_admission' => $funcionario1->contractultimate->date_of_admission,
                    'worked_days' => $funcionario1->personPayrollPayment->worked_days ?? '',
                    'transportation_assitance' => $funcionario1->personPayrollPayment->transportation_assistance ?? '',
                    'deducciones' => $tempDeducciones,
                    'retencion' => $retencion,
                    'ingresos_contitutivos' => $temIngresos['valor_constitutivos'],
                    'ingresos_no_contitutivos' => $temIngresos['valor_no_constitutivos'],
                    'novedades' => $funcionario->novedades,
                    'horas_extras' => $tempExtras['horas_reportadas'],
                    'nro_horas_extras' => array_sum(array_values($tempExtras['horas_reportadas'])),
                    'novedades' => $tempNovedades,
                    'valor_ingresos_salariales' => ($temIngresos['valor_constitutivos'] +
                        $tempNovedades['valor_total'] + $extras),
                    'valor_ingresos_no_salariales' => $temIngresos['valor_no_constitutivos'],
                    'valor_deducciones' => $tempDeducciones['valor_total']
                ];
            }

            $totalCostoEmpresa += $totalSalarios + $totalRetenciones + $totalSeguridadSocial + $totalParafiscales + $totalProvisiones;
            $response = [
                'frecuencia_pago' => $frecuenciaPago,
                'inicio_periodo' => $fechaInicioPeriodo,
                'fin_periodo' => $fechaFinPeriodo,
                'salarios' => $totalSalarios,
                'seguridad_social' => $totalSeguridadSocial,
                'parafiscales' => $totalParafiscales,
                'provisiones' => $totalProvisiones,
                'extras' => $totalExtras,
                'ingresos' => $totalIngresos,
                'retenciones' => $totalRetenciones,
                'costo_total_empresa' => $totalCostoEmpresa,
                'nomina_paga' => $paga,
                'nomina' => $nomina,
                'nomina_paga_id' => $idNominaExistente,
                'code' => $nomina && $nomina->code ? $nomina->code : '',
                'total_funcionarios' => count($funcionariosResponse),
                'email_reported' => $nomina->email_reported ?? '',
                'funcionarios' => $funcionariosResponse
            ];
            if ($return_type == 'success') {
                return $this->success($response);
            } else if ($return_type == 'collection') {
                return $response;
            }
        } catch (\Throwable $th) {
            return $this->error(['Line: ' . $th->getLine() . ' - File: ' . $th->getFile() . ' Msg: ' . $th->getMessage()], 204);
        }
    }

    public function payPeople($inicio = null, $fin = null)
    {
        try {
            $companyId = Company::find($this->getCompany())->id;
            $frecuenciaPago = Company::find($this->getCompany())['payment_frequency'];
            $fechaInicioPeriodo = Carbon::now()->startOfMonth()->format("Y-m-d H:i:s");
            $fechaFinPeriodo = Carbon::now()->endOfMonth()->format("Y-m-d H:i:s");
            $funcionariosResponse = [];

            if ($frecuenciaPago === 'Quincenal') {
                if (date("Y-m-d") > date("Y-m-15 00:00:00")) {
                    $fechaInicioPeriodo = date("Y-m-16 00:00:00");
                } else {
                    $fechaFinPeriodo = date("Y-m-15 23:59:59");
                    ;
                }
            }

            $fechaInicioPeriodo = $fechaInicioPeriodo;
            $fechaFinPeriodo = $fechaFinPeriodo;

            /**
             * Comprobar si los parámetros inicio y fin no son nulos, si no lo son, entonces significa
             * que se requiere ver o actualizar una nómina antigua ya que estos valores solo son asignados
             * desde el componente de historial de pagos en Vue cuando se realiza una petición.
             */
            if ($inicio) {
                $fechaInicioPeriodo = $inicio;
            }

            if ($fin) {
                $fechaFinPeriodo = $fin;
            }
            $fechasNovedades = function ($query) use ($fechaInicioPeriodo, $fechaFinPeriodo) {
                return $query->whereBetween('date_start', [$fechaInicioPeriodo, $fechaFinPeriodo])->whereBetween('date_end', [$fechaInicioPeriodo, $fechaFinPeriodo])->with('disability_leave');
            };


            $funcionarios = Person::with('contractultimate')
                ->whereHas('contractultimate', function ($query) use ($fechaInicioPeriodo, $fechaFinPeriodo, $companyId) {
                    $query->where(function ($subquery) use ($fechaInicioPeriodo, $fechaFinPeriodo) {
                        $subquery->whereDate('date_of_admission', '<=', $fechaFinPeriodo)
                            ->whereDate('date_end', '>=', $fechaInicioPeriodo)
                            ->orWhereNull('date_end');
                    })
                        ->where(function ($subquery) use ($companyId) {
                            $subquery->where('company_id', $companyId)
                                ->where('liquidated', '0');
                        });
                })
                ->with(['payroll_factors' => $fechasNovedades])
                ->get();


            foreach ($funcionarios as $funcionario) {
                if ($funcionario instanceof Person) {
                    $funcionariosResponse[] = $this->success([
                        'id' => $funcionario->id,
                        'identifier' => $funcionario->identifier,
                        'first_name' => $funcionario->first_name,
                        'first_surname' => $funcionario->first_surname,
                        'image' => $funcionario->image,
                        'city' => $funcionario->place_of_birth,
                        'basic_salary' => $funcionario->contractultimate->salary,
                        'position' => $funcionario->contractultimate->position->name,
                        'salario_neto' => $this->getPagoNeto($funcionario, $fechaInicioPeriodo, $fechaFinPeriodo)['total_valor_neto'],
                        'horas_extras' => $this->getExtrasTotales($funcionario, $fechaInicioPeriodo, $fechaFinPeriodo)->horas_reportadas,
                        'novedades' => $this->getNovedades($funcionario, $fechaInicioPeriodo, $fechaFinPeriodo)['novedades'],

                        'valor_ingresos_salariales' => ($this->getIngresos($funcionario, $fechaInicioPeriodo, $fechaFinPeriodo)['valor_constitutivos'] +
                            $this->getNovedades($funcionario, $fechaInicioPeriodo, $fechaFinPeriodo)['valor_total'] +
                            $this->getExtrasTotales($funcionario, $fechaInicioPeriodo, $fechaFinPeriodo)->valor_total),
                        'valor_ingresos_no_salariales' => $this->getIngresos($funcionario, $fechaInicioPeriodo, $fechaFinPeriodo)['valor_no_constitutivos'],
                        'valor_deducciones' => $this->getDeducciones($funcionario, $fechaInicioPeriodo, $fechaFinPeriodo)->valor_total
                    ]);
                }
            }
            return $funcionariosResponse;
        } catch (\Throwable $th) {

            return [$th->getLine() . ' ' . $th->getFile() . ' ' . $th->getMessage()];
        }
    }

    public function getPagoNeto(Person $id, $fechaInicio, $fechaFin)
    {
        return NominaPago::pagoFuncionarioWithPerson($id)
            ->fromTo($fechaInicio, $fechaFin)
            ->calculate();
    }
    public function getPagoNetoWithParams(Person $id, $salarioBase, $tempExtras, $tempNovedades, $temIngresos, $retencion, $tempDeducciones, $fechaInicio, $fechaFin)
    {
        return NominaPago::pagoFuncionarioWithPerson($id)
            ->withParams($salarioBase, $tempExtras, $tempNovedades, $temIngresos, $retencion, $tempDeducciones, $fechaInicio, $fechaFin)
            ->calculate();
    }

    /**
     * Calcular la cantidad y total de horas extras y recargos acumulados del funcionario en el periodo
     *
     * @param int $id
     * @param string $fechaInicio
     * @param string $fechaFin
     * @return Illuminate\Support\Collection
     */
    public function getExtrasTotales(Person $id, $fechaInicio, $fechaFin)
    {
        return PayrollOvertime::extrasFuncionarioWithPerson($id)->fromTo($fechaInicio, $fechaFin);
    }

    /**
     * Calcular la cantidad y total de novedades del funcionario en el periodo
     *
     * @param int $id
     * @param string $fechaInicio
     * @param string $fechaFin
     */
    public function getNovedades(Person $id, $fechaInicio, $fechaFin)
    {
        return NominaNovedades::novedadesFuncionarioWithPerson($id)
            ->fromTo($fechaInicio, $fechaFin)
            ->calculate();
    }

    public function getIngresos(Person $id, $fechaInicio, $fechaFin)
    {
        return NominaIngresos::ingresosFuncionarioWithPerson($id)
            ->fromTo($fechaInicio, $fechaFin)
            ->calculate();
    }

    public function getDeducciones(Person $id, $fechaInicio, $fechaFin)
    {
        return NominaDeducciones::deduccionesFuncionarioWithPerson($id)
            ->fromTo($fechaInicio, $fechaFin)
            ->calculate();
    }

    public function getSeguridad(Person $id, $fechaInicio, $fechaFin)
    {
        return NominaSeguridad::seguridadFuncionarioWithPerson($id)
            ->fromTo($fechaInicio, $fechaFin)
            ->calculate();
    }
    public function getSeguridadWithParams(Person $id, $fechaInicio, $fechaFin, $retencion, $tempNovedades)
    {
        return NominaSeguridad::seguridadFuncionarioWithPerson($id)
            ->withParams($retencion, $tempNovedades, $fechaInicio, $fechaFin)
            ->calculate();
    }

    public function getRetenciones(Person $id, $fechaInicio, $fechaFin)
    {
        return NominaRetenciones::retencionesFuncionarioWithPerson($id)
            ->fromTo($fechaInicio, $fechaFin)
            ->calculate();
    }
    public function getRetencioneswithParams(Person $id, $fechaInicio, $fechaFin, $salarioBase, $tempExtras, $tempNovedades, $temIngresos)
    {
        return NominaRetenciones::retencionesFuncionarioWithPerson($id)
            ->withParams($salarioBase, $tempExtras, $tempNovedades, $temIngresos, $fechaInicio, $fechaFin)
            ->calculate();
    }

    public function getProvisiones(Person $id, $fechaInicio, $fechaFin)
    {
        return NominaProvisiones::provisionesFuncionarioWithPerson($id)
            ->fromTo($fechaInicio, $fechaFin)
            ->calculate();
    }
    public function getProvisionesWithParams(Person $id, $fechaInicio, $fechaFin, $salarioBase, $tempExtras, $tempNovedades, $retencion)
    {
        return NominaProvisiones::provisionesFuncionarioWithPerson($id)
            ->withParams($salarioBase, $tempExtras, $tempNovedades, $retencion, $fechaInicio, $fechaFin)
            ->calculate();
    }

    public function getSalario(Person $id, $fechaInicio, $fechaFin)
    {
        return NominaSalario::salarioFuncionarioWithPerson($id)
            ->fromTo($fechaInicio, $fechaFin)
            ->calculate();
    }

    public function getPorcentajes()
    {
        $company = Company::with('payrollSocialSecurityPerson.payroll')->find($this->getCompany());
        $data = new Collection();
        foreach ($company->payrollSocialSecurityPerson as $payrollSocialSecurityPerson) {
            $concept = $payrollSocialSecurityPerson->payroll->concept;
            $percentage = $payrollSocialSecurityPerson->percentage;
            $data->push([
                'concept' => $concept,
                'percentage' => $percentage
            ]);
        }
        return response($data, 200);
    }

    public function dianReport(Request $request)
    {
        $payroll = PayrollPayment::with('personPayrollPayment.person.contractultimate')
            ->find($request->payroll_payment_id);
        $people = $payroll->personPayrollPayment;
        //return($payroll);
        $response = array(
            "cune" => '34534534534533',
            "dianResponse" => [
                "governmentResponse" => [
                    "code" => "00",
                    "message" => "Procesado Correctamente.",
                    "errorMessages" => []
                ]
            ]
        );
        return $this->success($response);
        $i = 0;
        foreach ($people as $person) {
            $i++;
            $mes_actual = date('m', strtotime($payroll['start_period']));
            $anio_actual = date('Y', strtotime($payroll['start_period']));
            $mensualidad = "'$anio_actual-$mes_actual%'";
            $quincena = "%";

            $fini = $payroll['start_period'];
            $ffin = $payroll['end_period'];
            $admission = $person->person->contractultimate->date_of_admission;


            $ini = $fini;
            if ($admission > $fini) {
                $ini = $admission;
            }
            $fin = $ffin;

            $funcionario = new CalculoNomina(
                $person['Identificacion_Funcionario'],
                $quincena,
                $ini,
                $fin,
                'Nomina',
                $person['Tipo_Nomina'],
                $mensualidad
            );
            $funcionario = $funcionario->CalculosNomina(false);
            unset($oItem);
            $funcionarios[$i]['type_document_id'] = 7;
            $funcionarios[$i]['resolution_number'] = 2;
            $funcionarios[$i]['resolution_id'] = 7;
            $funcionarios[$i]['date_pay'] = date("Y-m-d", strtotime($person["Fecha"]));
            $funcionarios[$i]['date'] = date("Y-m-d", strtotime($person["Fecha"]));
            $hour = new Carbon($person["Fecha"]);
            $funcionarios[$i]['hour'] = $hour->format('H:i:s') . '-05:00';
            $funcionarios[$i]['payroll_period'] = $person["Tipo_Nomina"];
            $totalDevengados = $funcionario["datos_dian"]["totalDian"]["Valor"] + $funcionario["Deducciones"];
            $configPrefixCune = $this->getConfigPrefix($person["Fecha"], $totalDevengados, $funcionario["Deducciones"], $funcionario["datos_dian"]["totalDian"]["Valor"], $person['Identificacion_Funcionario'], $hour);
            $funcionarios[$i]['cune_propio'] = hash('sha384', $configPrefixCune[2]);
            $funcionarios[$i]['observation'] = "Nomina Electronica";
            $funcionarios[$i]['prefix'] = $configPrefixCune[1];
            $funcionarios[$i]['number'] = $configPrefixCune[0];
            $funcionarios[$i]['code'] = $configPrefixCune[1] . $configPrefixCune[0];
            $funcionarios[$i]['file'] = $configPrefixCune[1] . $configPrefixCune[0];
            $funcionarios[$i]['date_start_period'] = $person["Fecha_Inicio"];
            $funcionarios[$i]['date_end_period'] = $person["Fecha_Fin"];
            $funcionarios[$i]["integration_date"] = date('Y-m-d', strtotime($funcionario["Contrato"]["Fecha_Inicio_Contrato"]));
            $funcionarios[$i]['person']["historic_worked_time"] = $funcionario["tiempo_laborado"];
            $funcionarios[$i]['person']["salary"] = $funcionario["Contrato"]["Valor"];  //valor contrato
            $funcionarios[$i]['person']["code"] = $funcionario["Contrato"]["CodigoFun"];
            $funcionarios[$i]['person']["work_contract_type"]["code"] = $funcionario["Contrato"]["Cod_Dian"];
            $funcionarios[$i]['person']["salary_integral"] = "true";
            $funcionarios[$i]['person']["worker_type"]["code"] = $funcionario["Contrato"]["Codigo_Tipo"];
            $funcionarios[$i]['person']["worker_subtype"]["code"] = "00";
            $funcionarios[$i]['person']["work_contract_type"]["code"] = 1;
            $funcionarios[$i]['person']['high_risk_pension'] = "false";
            $funcionarios[$i]['person']['identifier'] = $person["Identificacion_Funcionario"];
            $apellidos = explode(" ", $person["Apellidos"]);
            if (count($apellidos) > 2) {
                $seg_apellido = implode(" ", array_splice($apellidos, 1, count($apellidos)));
                $apellidos[1] = $seg_apellido;
            }
            if (!isset($apellidos[1]))
                $apellidos[1] = ".";
            $funcionarios[$i]['person']['first_name'] = $person['Nombres'];
            $funcionarios[$i]['person']['last_name'] = $apellidos[0];
            $funcionarios[$i]['person']['last_names'] = $apellidos[1];
            $funcionarios[$i]['person']['type_document_identification']['code'] = $person["Cod_Dian"];
            $funcionarios[$i]['person']['work_place']['country']['id'] = 46;
            $funcionarios[$i]['person']['work_place']['country']['code'] = "CO";
            $funcionarios[$i]['person']['work_place']['municipality']['id'] = 46;
            $funcionarios[$i]['person']['work_place']['municipality']['code'] = "68001";
            $funcionarios[$i]['person']['work_place']['municipality']['department']['code'] = "68";
            $funcionarios[$i]['person']['work_place']['addres'] = "Prueba";
            $funcionarios[$i]['pay']["payroll_pay_formate"] = [];
            $funcionarios[$i]['pay']["payroll_pay_formate"]["code"] = 1;
            $funcionarios[$i]['pay']["payroll_pay_method"]["code"] = "1";
            $funcionarios[$i]['accrued']["basic"]["worked_days"] = $funcionario["Dias_Laborados"];
            $funcionarios[$i]['accrued']["basic"]["salary_payroll"] = $funcionario["Total_Quincena"]; //pagado
            if ($funcionario["datos_dian"]["AuxilioTransporte"]["Valor"] != 0) {
                $funcionarios[$i]['accrued']["transport_subsidy"]["salarial"] = round($funcionario["datos_dian"]["AuxilioTransporte"]["Valor"]);
            }
            foreach ($funcionario["Lista_Novedades"] as $ln) {
                if ($ln["Id_Tipo_Novedad"] == 2 || $ln["Id_Tipo_Novedad"] == 3) {
                    $inabi = [];
                    $inabi = ["date_start" => date('Y-m-d', strtotime($ln["Fecha_Inicio"])), "date_end" => date('Y-m-d', strtotime($ln["Fecha_Inicio"])), "days" => $ln["Dias"], "type" => $ln["Codigo_Dian"], "value" => round(($funcionario["Sueldo"] * $ln["Dias"]) / 30)];
                    $funcionarios[$i]['accrued']["inability"][] = $inabi;
                    continue;
                }
                if ($ln["Id_Tipo_Novedad"] == 4 || $ln["Id_Tipo_Novedad"] == 5) {
                    $novedadmp = [];
                    $novedadmp = ["date_start" => date('Y-m-d', strtotime($ln["Fecha_Inicio"])), "date_end" => date('Y-m-d', strtotime($ln["Fecha_Inicio"])), "days" => $ln["Dias"], "type" => $ln["Tipo_Novedad"], "value" => 0];
                    $funcionarios[$i]['accrued']["licences"]["mp"][] = $novedadmp;
                    continue;
                }
                if ($ln["Id_Tipo_Novedad"] == 6) {
                    $novedadr = [];
                    $novedadr = ["date_start" => date('Y-m-d', strtotime($ln["Fecha_Inicio"])), "date_end" => date('Y-m-d', strtotime($ln["Fecha_Inicio"])), "days" => $ln["Dias"], "type" => $ln["Tipo_Novedad"], "value" => 0];
                    $funcionarios[$i]['accrued']["licences"]["r"][] = $novedadr;
                    continue;
                }
                if ($ln["Id_Tipo_Novedad"] == 7) {
                    $novedadnr = [];
                    $novedadnr = ["date_start" => date('Y-m-d', strtotime($ln["Fecha_Inicio"])), "date_end" => date('Y-m-d', strtotime($ln["Fecha_Inicio"])), "days" => $ln["Dias"], "type" => $ln["Tipo_Novedad"], "value" => 0];
                    $funcionarios[$i]['accrued']["licences"]["nr"][] = $novedadnr;
                    continue;
                }
            }
            foreach ($funcionario["Bono_Funcionario"] as $bn) {
                ;
                if ($bn['Valor'] == 0)
                    $bn['Valor'] = null;
                if ($bn['Tipo_Bono'] == 2) {
                    $valorb = [];
                    $valorb = ["salarial" => $bn["Valor"]];
                    $funcionarios[$i]['accrued']["bonus"][] = $valorb;
                } else {
                    $b = [];
                    $valorb = ["no_salarial" => $bn["Valor"]];
                    $funcionarios[$i]['accrued']["bonus"][] = $valorb;
                }
            }
            foreach ($funcionario["Lista_Ingresos_No_Salariales"] as $nsa) {
                if ($nsa['Valor'] > 0) {
                    if ($nsa["Id_Concepto_Parametro_Nomina"] == 17 || $nsa["Id_Concepto_Parametro_Nomina"] == 20 || $nsa["Id_Concepto_Parametro_Nomina"] == 25 || $nsa["Id_Concepto_Parametro_Nomina"] == 26) {
                        $valor = [];
                        $valor = ["no_salarial" => $nsa["Valor"]];
                        $funcionarios[$i]['accrued']["assistances"][] = $valor;

                        continue;
                    }
                    if ($nsa["Id_Concepto_Parametro_Nomina"] == 18 || $nsa["Id_Concepto_Parametro_Nomina"] == 19 | $nsa["Id_Concepto_Parametro_Nomina"] == 21 || $nsa["Id_Concepto_Parametro_Nomina"] == 22 || $nsa["Id_Concepto_Parametro_Nomina"] == 23 || $nsa["Id_Concepto_Parametro_Nomina"] == 24) {
                        $valor1 = [];
                        $valor1 = ["description" => $nsa["Nombre"], "no_salarial" => $nsa["Valor"], "salarial" => 1];
                        $funcionarios[$i]['accrued']["others"][] = $valor1;
                    }
                    $vCompensations = [];
                    $vCompensations = ["value_ordanary" => 1, "value_extra_ordanary" => "0"];
                    $funcionarios[$i]['accrued']["compensations"][] = $vCompensations;
                    $vcommissions = [];
                    $vcommissions = ["value" => 1];
                    $funcionarios[$i]['accrued']["commissions"][] = $vcommissions;
                    $vthird = [];
                    $vthird = ["value" => 1];
                    $funcionarios[$i]['accrued']["third_payments"][] = $vthird;
                    $vAdvance = [];
                    $vAdvance = ["value" => 1];
                    $funcionarios[$i]['accrued']["advances"][] = $vAdvance;
                }
            }
            $s = -1;
            foreach ($funcionario["Lista_Ingresos_Salariales"] as $sa) {
                $s++;
                if ($sa["Valor"] > 0)
                    $funcionarios[$i]['accrued']["assistances"][$s]["salarial"] = $sa["Valor"];
            }
            $funcionarios[$i]["deductions"]["healt"] = $funcionario["datos_dian"]["Salud"];
            $funcionarios[$i]["deductions"]["pension_funds"] = $funcionario["datos_dian"]["Pension"];
            if ($funcionario["Prestamos_Funcionario"]['Valor_Cuota'] != 0) {
                foreach ($funcionario["Prestamos_Funcionario"] as $pr) {
                    ;
                    $bn = [];
                    $bn = ["description" => $pr['Observaciones'], "value" => $pr['Valor_Cuota']];
                    $funcionarios[$i]["deductions"]["loans"][] = $bn;
                }
            }
            if ($funcionario["datos_dian"]["OtrasDeducciones"] != 0) {
                $funcionarios[$i]["deductions"]["other_deductions"][0]["value"] = $funcionario["datos_dian"]["OtrasDeducciones"];
            }
            if ($funcionario["datos_dian"]["PensionV"]["Valor"] != 0) {
                $funcionarios[$i]["deductions"]["voluntary_pension"] = $funcionario["datos_dian"]["PensionV"]["Valor"];
            }
            if ($funcionario["datos_dian"]["Retencion"]["Valor"] > 0) {
                $funcionarios[$i]["deductions"]["source_retention"] = number_format($funcionario["datos_dian"]["Retencion"]["Valor"], 2, '.', '');
            }
            $funcionarios[$i]["totals"]["rounded"] = "0";
            $funcionarios[$i]["totals"]["accrued"] = number_format($funcionario["datos_dian"]["totalDian"]["Valor"] + $funcionario["Deducciones"], 2, '.', '');
            $funcionarios[$i]["totals"]["deductions"] = number_format($funcionario["Deducciones"], 2, '.', '');
            $funcionarios[$i]["totals"]["voucher"] = number_format($funcionario["datos_dian"]["totalDian"]["Valor"], 2, '.', '');
            $respuesta_dian = /* GetApi($funcionarios[$i], $funcionario = false, $note = false) */'';
            $cune = $respuesta_dian["Cune"];
            $estado = '';
            if (strpos($respuesta_dian["Respuesta"], "procesado anteriormente") !== false) {
                $estado = "true";
            } else {
                $estado = $respuesta_dian["Procesada"];
            }
            if ($estado == "true") {
                //actualizarConfig();
            }
            $respuesta_endpoint[$person["Identificacion_Funcionario"]] = array(
                "cune" => $cune,
                "procesada" => $estado,
                "respuesta" => $respuesta_dian
            );
            //actualizarNominaFuncionario($cune, $estado, $func["Id_Nomina_Funcionario"], $funcionarios[$i]['code']);
            //nominaElectronicaFuncionario($func["Identificacion_Funcionario"], $func["Id_Nomina_Funcionario"], $respuesta_dian, $cune, $funcionarios[$i]['code']);
            sleep("3");
        }
        echo json_encode($respuesta_endpoint);
    }

    public function detailsDianReport($id)
    {
        $employees = PersonPayrollPayment::with('electronicPayroll', 'personBasic')
            ->whereHas('electronicPayroll')
            ->where('payroll_payment_id', $id)
            ->paginate(request()->get('pageSize', 1), ['*'], 'page', request()->get('page', 1));

        $employees2 = PersonPayrollPayment::with('electronicPayroll', 'personBasic')
            ->whereHas('electronicPayroll')
            ->where('payroll_payment_id', $id)
            ->get();

        $result = [
            'total_reported' => count($employees2),
            'total_success' => $employees2->where('electronicPayroll.status', 'Exito')->count(),
            'total_error' => $employees2->where('electronicPayroll.status', 'Error')->count(),
            'total_pending' => $employees2->where('electronicPayroll.status', 'Pendiente')->count(),
        ];

        return $this->successPaginate($employees, $result);
    }

    public function getConfigPrefix()
    {

    }
}
