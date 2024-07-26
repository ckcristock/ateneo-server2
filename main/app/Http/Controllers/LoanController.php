<?php

namespace App\Http\Controllers;

use App\Exports\LoanExport;
use App\Models\Company;
use App\Models\Loan;
use App\Services\LoanService;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Services\consulta;
use App\Models\Person;
use Carbon\Carbon;
use NumberFormatter;
use Maatwebsite\Excel\Facades\Excel;

class LoanController extends Controller
{
    use ApiResponser;

    private function getCompany()
    {
        return Person::find(Auth()->user()->person_id)->company_worked_id;
    }
    public function index()
    {
        return $this->success(
            Loan::with("person")
                ->with("user")
                ->with("fees")
                ->get()
        );
    }

    public function paginate(Request $request)
    {
        $pageSize = $request->get('pageSize', 10);
        $page = $request->get('page', 1);

        $query = Loan::with("person", "user", 'fees')
            ->whereHas('person.contractultimate', function ($query) {
                $query->where('company_id', $this->getCompany());
            })
            ->orderByDesc('date');

        if ($request->filled('person_id')) {
            $query->where('person_id', $request->person_id);
        }

        if ($request->filled('end_date')) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        return $this->success($query->paginate($pageSize, ['*'], 'page', $page));
    }

    public function store(Request $request)
    {
        try {
            $datos = $request->all();
            if ($datos["type"] != "Libranza") {
                if ($datos["interest_type"] == "Prestamo") {
                    $dataProyeccion = LoanService::proyeccionAmortizacionAPrestamo(
                        $datos["value"],
                        $datos["monthly_fee"],
                        $datos["interest"],
                        $datos["payment_type"],
                        $datos["first_payment_date"]
                    );
                } else {
                    $dataProyeccion = LoanService::proyeccionAmortizacion(
                        $datos["value"],
                        $datos["monthly_fee"],
                        $datos["interest"],
                        $datos["payment_type"],
                        $datos["first_payment_date"]
                    );
                }
            } else {
                $dataProyeccion = LoanService::proyeccionAmortizacionLibranza(
                    $datos["value"],
                    $datos["monthly_fee"],
                    $datos["payment_type"],
                    $datos["first_payment_date"]
                );
                $dataProyeccion = LoanService::proyeccionAmortizacionL(
                    $datos["value"],
                    $datos["monthly_fee"],
                    $datos["payment_type"],
                    $datos["first_payment_date"]
                );
            }
            $datos["number_fees"] =
                $datos["pay_fees"] == "No"
                ? "1"
                : $dataProyeccion["number_fees"];
            $saldo = $datos["value"];

            $datos["outstanding_balance"] = $saldo;

            //if (array_key_exists('Id_Prestamo', $datos))  unset($datos['Id_Prestamo']);

            /* $oItem = new complex("Prestamo","Id_Prestamo");
        foreach ($datos as $index => $value) {
            $oItem->$index = $value;
        } */

            $datos["interest"] = number_format($datos["interest"], 2, ".", "");
            /*    $config = new Configuracion();
        $cod = $config->Consecutivo('Prestamo');
        $oItem->Codigo = $cod;
        unset($config);

        $oItem->save();
        $id_prestamo = $oItem->getId(); */

            $datos["user_id"] = auth()->user()->person_id;

            $loanDB = Loan::create($datos);
            LoanService::guardarCuotasPrestamo(
                $loanDB->id,
                $dataProyeccion["proyection"]
            );
            if ($datos["type"] == "Prestamo") {
                /*
                 *CONTABILIDAD
                 $datos['Nit'] = $datos['Empleado']['Identificacion_Funcionario'];
            $datos['Id_Registro'] = $id_prestamo;
            $datos['Id_Plan_Cuenta_Banco'] = $datos['Plan_Cuenta']['Id_Plan_Cuentas'];

                $con = new ContabilidadPrestamo();
            $con->CrearMovimientoContable('Prestamo', $datos);
            unset($con); */
            }

            return $this->success("Prestamo creado");
        } catch (\Throwable $th) {
            return $this->errorResponse(
                $th->getMessage() . $th->getLine() . $th->getFile(),
                401
            );
        }
    }

    public function loanpdf($id, $company_id)
    {
        $loan = DB::table('loans as l')
            ->select(
                'l.person_id',
                'p.first_name',
                'p.second_name',
                'p.first_surname',
                'p.second_surname',
                'p.identifier',
                'l.value',
                'l.interest',
                'l.monthly_fee',
                'l.interest_type',
                'l.number_fees',
                'l.payment_type',
                'l.code',
                'l.date'
            )
            ->join('people as p', function ($join) {
                $join->on('p.id', '=', 'l.person_id');
            })
            ->where('l.id', $id)
            ->first();
        $proyecciones = $this->proyeccionAmortizacion($loan->value, $loan->monthly_fee, $loan->interest, $loan->payment_type, $loan->date);
        $getTotalA = $this->getTotales($proyecciones['Proyeccion'], 'Amortizacion');
        $getTotalI = $this->getTotales($proyecciones['Proyeccion'], 'Intereses');
        $getTotalV = $this->getTotales($proyecciones['Proyeccion'], 'Valor_Cuota');
        $company = Company::where('id', $company_id)->first();
        $image = $company->page_heading;
        $datosCabecera = (object) array(
            'Titulo' => 'Amortización préstamo',
            'Codigo' => $loan->code,
            'Fecha' => $loan->date
        );
        $pdf = PDF::loadView('pdf.loanpdf', [
            'proyecciones' => $proyecciones,
            'funcionario' => $loan,
            'getTotalA' => $getTotalA,
            'getTotalI' => $getTotalI,
            'getTotalV' => $getTotalV,
            'company' => $company,
            'datosCabecera' => $datosCabecera,
            'image' => $image
        ]);
        return $pdf->download('loanpdf.pdf');
    }
    public function loanExcel($id, $company_id)
    {
        $loan = Loan::with('person')->find($id);
        $proyecciones = $this->proyeccionAmortizacion($loan->value, $loan->monthly_fee, $loan->interest, $loan->payment_type, $loan->date);
        $getTotalA = $this->getTotales($proyecciones['Proyeccion'], 'Amortizacion');
        $getTotalI = $this->getTotales($proyecciones['Proyeccion'], 'Intereses');
        $getTotalV = $this->getTotales($proyecciones['Proyeccion'], 'Valor_Cuota');
        $company = Company::where('id', $company_id)->first();
        $image = $company->page_heading;
        return Excel::download(new LoanExport($loan, $proyecciones, $getTotalA, $getTotalI, $getTotalV, $company), 'proyeccion.xlsx');
        /* $datosCabecera = (object) array(
            'Titulo' => 'Amortización préstamo',
            'Codigo' => $loan->code,
            'Fecha' => $loan->date
        ); */
    }

    public function getTotales($data, $tipo)
    {
        $datos = array_column($data, $tipo);
        $total = array_sum($datos);
        return $total;
    }

    public function proyeccionAmortizacion($prestamo, $cuota_mensual, $porcentaje_interes, $tipo_descuento = "Mensual", $primer_pago = '')
    {
        $saldo = $prestamo;
        $total_cuotas = 0;
        $proyeccion = [];
        if ($primer_pago == '') {
            $primer_pago = date("Y-m-d");
        }
        while ($saldo > 0) {
            $interes = $saldo * ($porcentaje_interes / 100);
            $cuota = $saldo >= $cuota_mensual ? $cuota_mensual : ($saldo + $interes);
            $amortizacion = $saldo < $cuota ? $saldo : ($cuota - $interes);
            $saldo = $saldo - $amortizacion;
            $total_cuotas++;
            $fecha = $this->calcularFechaDescuento($primer_pago, $total_cuotas, $tipo_descuento);
            $data = [
                "Cuota" => $total_cuotas,
                "Amortizacion" => number_format($amortizacion, 2, ".", ""),
                "Intereses" => number_format($interes, 2, ".", ""),
                "Valor_Cuota" => number_format($cuota, 2, ".", ""),
                "Saldo" => number_format($saldo, 2, ".", ""),
                "Fecha" => $fecha
            ];
            $proyeccion[] = $data;
        }

        $response['Proyeccion'] = $proyeccion;
        $response['Total_Cuotas'] = $total_cuotas;

        return $response;
    }
    public function calcularFechaDescuento($fecha_descuento, $cuota, $tipo = "Mensual")
    {
        $mes = 0;
        if ($tipo == "Mensual") {
            $suma = "+$mes months";
        } else {
            $num = $mes * 15;
            $suma = "+$num days";
        }
        if ($cuota == 1) {
            $response = $fecha_descuento;
        } else {
            $nueva_fecha = strtotime($suma, strtotime($fecha_descuento));
            $response = date('Y-m-d', $nueva_fecha);
        }
        $f = explode("-", $response);
        $nuevo_mes1 = strtotime($f[0] . "-" . $f[1]);
        if ($f[2] > 15) {
            $response = date('Y-m-', $nuevo_mes1) . date("d", (mktime(0, 0, 0, date("m", $nuevo_mes1) + 1, 1, date("Y", $nuevo_mes1)) - 1));
        } else {
            $response = date('Y-m-15', $nuevo_mes1);
        }

        return $response;
    }

    public function comprobarPrestamo()
    {
        $id = (isset($_REQUEST['empleado']) ? $_REQUEST['empleado'] : '');
        $tipo = (isset($_REQUEST['tipo']) ? $_REQUEST['tipo'] : '');

        $query = 'SELECT DISTINCT P.type, P.state, P.id
            FROM loans P
            INNER JOIN loan_fees PC ON P.id = PC.loan_id
            WHERE person_id = ' . $id . ' AND P.type = "' . $tipo . '" ';
        $oCon = new consulta();
        $oCon->setTipo('Simple');
        $oCon->setQuery($query);
        $response = $oCon->getData();
        unset($oCon);

        return response()->json($response);
    }

    public function pazSalvo($id, $company_id)
    {
        $loan = Loan::with('person')->find($id);
        $numberFormat = new NumberFormatter("es", NumberFormatter::SPELLOUT);
        $today = Carbon::now();
        $dia = Carbon::parse($today)->format("d");
        $mes = Carbon::parse($today)->locale('es')->translatedFormat('F');
        $diaEnletras = $numberFormat->format($dia);
        $company = Company::where('id', $company_id)->first();
        $datosCabecera = (object) array(
            'Titulo' => 'Paz y salvo',
            'Codigo' => $loan->code,
            'Fecha' => $loan->date
        );
        $pdf = PDF::loadView('pdf.loan_paz_salvo', [
            'data' => $loan,
            'company' => $company,
            'datosCabecera' => $datosCabecera,
            'image' => $company->page_heading,
            'mes' => $mes,
            'diaEnletras' => $diaEnletras,
        ]);
        return $pdf->download('loan_paz_salvo.pdf');
    }
}
