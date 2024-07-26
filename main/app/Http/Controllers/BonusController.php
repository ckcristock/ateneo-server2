<?php

namespace App\Http\Controllers;

use App\Clases\stdObject;
use App\Models\Bonus;
use App\Models\BonusPerson;
use App\Models\Person;
use App\Exports\BonusExport;
use App\Traits\ApiResponser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class BonusController extends Controller
{
    use ApiResponser;

    private function getCompany()
    {
        return Person::find(Auth()->user()->person_id)->company_worked_id;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->success(Bonus::all());
    }

    public function paginate(Request $request)
    {
        return $this->success(
            Bonus::orderByDesc('period')
                ->where('company_id', $this->getCompany())
                ->when($request->period, function ($query, $fill) {
                    $query->where('period', 'like', "%$fill%");
                })
                ->paginate(
                    request()->get('pageSize', 5),
                    ['*'],
                    'page',
                    request()->get('page', 1)
                )
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $data = $request->all();
        $id = $data['funcionario'];
        $responsable = Person::select(
            'id',
            'first_name',
            'second_name',
            'first_surname',
            'second_surname',
            'identifier'
        )->find($id);

        $nuevo = Bonus::updateOrCreate([
            'period' => $request->period
        ], [
            'total_bonuses' => $data['total_primas'],
            'total_employees' => $data['total_empleados'],
            'payment_date' => Carbon::now(),
            'period' => $data['period'],
            'status' => $data['status'],
            'payer' => $data['funcionario'],
            'payer_identifier' => $responsable['identifier'],
            'payer_fullname' => ($responsable['first_name'] . ' ' .
                $responsable['second_name'] . ' ' .
                $responsable['first_surname'] . ' ' .
                $responsable['second_surname']),
            'company_id' => $this->getCompany()
        ]);

        $empleados = $data['empleados'];
        foreach ($empleados as $empleado) {
            $person_bonus = BonusPerson::updateOrCreate([
                'bonus_id' => $nuevo->id,
                'person_id' => $empleado['id'],
            ], [
                'bonus_id' => $nuevo->id,
                'person_id' => $empleado['id'],
                'identifier' => $empleado['identifier'],
                'fullname' => $empleado['first_name'] . ' ' .
                    $empleado['second_name'] . ' ' .
                    $empleado['first_surname'] . ' ' .
                    $empleado['second_surname'],
                'worked_days' => $empleado['worked_days'],
                'amount' => $empleado['bonus'],
                'lapse' => $data['period'],
                'average_amount' => $empleado['avg_salary'],
                'payment_date' => $nuevo['payment_date']
            ]);
        }
        return ($nuevo->wasRecentlyCreated && $person_bonus->wasRecentlyCreated)
            ? $this->success(['message' => 'Creado con éxito', "responsable" => $responsable])
            : $this->success(['message' => 'Actualizado con éxito', "responsable" => $responsable]);
    }

    public function checkBonuses($period)
    {
        $bonus = Bonus::where('period', $period)
            ->where('company_id', $this->getCompany())
            ->first();
        return $this->success($bonus);
    }

    public function consultaPrima(Request $request)
    {
        $period = $request->period;
        $existe = Bonus::where('period', $period)
            ->where('company_id', $this->getCompany())
            ->with('bonusPerson', 'personPayer')
            ->first();
        if ($existe) {
            $empleados = array();
            foreach ($existe->bonusPerson as $bonus) {
                $empleado = $bonus->person;
                $empleado->avg_salary = $bonus->average_amount;
                $empleado->worked_days = $bonus->worked_days;
                $empleado->bonus = $bonus->amount;
                array_push($empleados, $empleado);
            }
            $existe->empleados = $empleados;
            $existe->total_primas = $existe->total_bonuses;
            return $this->success($existe);
        } else {
            return $this->success($this->calcularPrimas($request));
        }
    }

    private function calcularPrimas(Request $request)
    {
        /// cuando se seleccione el período en el front, se deben enviar estas fechas,
        //para comparar con las fechas del contrato si son mayores o menores
        $fecha_inicio = Carbon::create($request->fecha_inicio);
        $fecha_fin = Carbon::create($request->fecha_fin);
        $employees = Person::whereHas('contractultimate', function ($query) {
            $query->where('company_id', $this->getCompany());
        })
            ->get();
        $total_primas = 0;
        $recorridos = 0;

        foreach ($employees as $employee) {
            $sum_salary = 0;
            $worked_days = 0;
            $nro_contracts = count($employee->work_contracts);

            foreach ($employee->work_contracts as $contract) {
                $admission = (is_null($contract->date_of_admission)) ? null : Carbon::create($contract->date_of_admission);
                $end = (is_null($contract->date_end)) ? null : Carbon::create($contract->date_end);
                $sum_salary += $contract->salary;

                if (!is_null($end) && ($end >= $fecha_inicio)) {  // si finalizó antes del inicio del periodo, no suma
                    //si la fecha de finalización ($end) no es null, es un contrato que ya terminó
                    //debe evaluarse si se encuentra en el periodo a calcular

                    if ($admission <= $fecha_inicio) {
                        if ($end >= $fecha_fin) {
                            $worked_days += $fecha_fin->diffInDays($fecha_inicio);
                        } elseif ($end <= $fecha_fin) {
                            $worked_days += $end->diffInDays($fecha_inicio);
                        }
                    } elseif ($admission >= $fecha_inicio) {
                        if ($end >= $fecha_fin) {
                            $worked_days += $fecha_fin->diffInDays($admission);
                        } elseif ($end <= $fecha_fin) {
                            $worked_days += $end->diffInDays($admission);
                        }
                    }
                } elseif (is_null($end)) {
                    //si es nullo, puede ser un contrato indefinido
                    if ($admission <= $fecha_inicio) {        //la fecha de admisión es antes del periodo calculado
                        $worked_days += $fecha_fin->diffInDays($fecha_inicio);
                    } elseif ($admission >= $fecha_inicio) {  //la fecha de admisión es luego del periodo calculado
                        $worked_days += $fecha_fin->diffInDays($admission);
                    }
                }
                $recorridos++;
            }

            $employee->nro_contratos = $nro_contracts;
            $employee->avg_salary = ($nro_contracts == 0) ? 0 : $sum_salary / $employee->nro_contratos;
            $employee->worked_days = ($worked_days > 180) ? 180 : $worked_days;
            $employee->bonus = ($employee->avg_salary / 360) * $employee->worked_days;
            $total_primas += $employee->bonus;
        }

        return [
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin,
            'total_primas' => $total_primas,
            'total_empleados' => count($employees),
            'recorridos' => $recorridos,
            'status' => 'pendiente',
            'periodo' => $request->period,
            'empleados' => $employees,
        ];
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Bonus  $bonus
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Bonus $bonus)
    {
        $bonus = Bonus::with('bonusPerson', 'personPayer')->get();
        return $this->success($bonus);
    }

    /**
     * To download report
     */
    public function reportBonus($anio, $period, $pagado)
    {
        if ($pagado) {
            $bonuses = Bonus::where('period', $anio . '-' . $period)
                ->where('company_id', $this->getCompany())
                ->with('bonusPerson', 'personPayer')
                ->first();
            return Excel::download(new BonusExport($bonuses), 'bonus_report.xlsx');
        } else {
            $fechaInicio = "";
            $fechaFin = "";
            if ($period == 1) {
                $fechaInicio = "01/01/" . $anio;
                $fechaFin = "06/30/" . $anio;
            } else {
                $fechaInicio = "07/01/" . $anio;
                $fechaFin = "12/30/" . $anio;
            }
            $datos = new Request([
                "fecha_inicio" => $fechaInicio,
                "fecha_fin" => $fechaFin,
                "funcionario" => 1,
                "period" => $anio . '-' . $period
            ]);
            $primas = $this->calcularPrimas($datos);

            $bonusPerson = array();

            foreach ($primas['empleados'] as $empleado) {
                $nuevo = new \stdClass();
                $nuevo->identifier = $empleado->identifier;
                $nuevo->fullname = $empleado->first_name . ' ' . $empleado->second_name . ' ' . $empleado->first_surname . ' ' . $empleado->second_surname;
                $nuevo->average_amount = $empleado->avg_salary;
                $nuevo->worked_days = $empleado->worked_days;
                $nuevo->amount = $empleado->bonus;

                array_push($bonusPerson, $nuevo);
            }
            $reportPrimas = new \stdClass();
            $reportPrimas->total_bonuses = $primas['total_primas'];
            $reportPrimas->total_employees = $primas['total_empleados'];
            $reportPrimas->period = $primas['periodo'];
            $reportPrimas->payment_date = null;
            $reportPrimas->status = 'pendiente';
            $reportPrimas->bonusPerson = $bonusPerson;

            return Excel::download(new BonusExport($reportPrimas), 'bonus_report.xlsx');
        }
    }

    public function pdfGenerate($anio, $period)
    {

        $data = Bonus::with('bonusPerson', 'personPayer')
            ->where('period', $anio . '-' . $period)
            ->where('company_id', $this->getCompany())
            ->first();

        //nota, se le agrega a cada uno para que sirva desde el coponente individual
        foreach ($data->bonusPerson as $bonus) {
            $bonus->fecha_inicio = ($period == 1) ? '01 Enero ' . $anio : '01 Julio ' . $anio;
        }

        $pdf = PDF::loadView('pdf.bonus_tickets', ['data' => $data])
            ->setPaper([0, 0, 614.295, 397.485]);

        return $pdf->download('colilla_prima.pdf');
    }
}
