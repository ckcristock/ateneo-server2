<?php

namespace App\Http\Controllers;

use App\Models\DocumentoContable;
use Illuminate\Http\Request;
use App\Http\Services\HttpResponse;
use App\Http\Services\consulta;
use App\Http\Services\complex;
use App\Models\Person;
use Maatwebsite\Excel\Facades\Excel;
use Exception;
use App\Http\Services\comprobantes\ObtenerProximoConsecutivo;
use App\Http\Services\resumenretenciones\funciones;
use App\Models\CuentaDocumentoContable;
use App\Models\MovimientoContable;
use App\Models\ThirdParty;
use Illuminate\Support\Facades\DB;
use App\Traits\ApiResponser;
use Barryvdh\DomPDF\Facade\Pdf;

class DocumentoContableController extends Controller
{
    use ApiResponser;

    public function paginate()
    {
        $query = CuentaDocumentoContable::select(
            'Documento_Contable.Estado',
            'Documento_Contable.Id_Documento_Contable',
            DB::raw("DATE_FORMAT(Documento_Contable.Fecha_Documento, '%d/%m/%Y') AS Fecha"),
            'Documento_Contable.Codigo',
            'Documento_Contable.Beneficiario',
            DB::raw("(CASE Documento_Contable.Tipo_Beneficiario
                WHEN 'Cliente' THEN (SELECT IF(social_reason IS NULL OR social_reason = '', CONCAT_WS(' ', first_name, second_name, first_surname, second_surname), social_reason) FROM third_parties WHERE id = Documento_Contable.Beneficiario AND is_client = 1)
                WHEN 'Proveedor' THEN (SELECT IF(social_reason IS NULL OR social_reason = '', CONCAT_WS(' ', first_name, second_name, first_surname, second_surname), social_reason) FROM third_parties WHERE id = Documento_Contable.Beneficiario AND is_supplier = 1)
                WHEN 'Funcionario' THEN (SELECT CONCAT_WS(' ', first_name, first_surname) FROM people WHERE id = Documento_Contable.Beneficiario)
            END) AS Tercero"),
            DB::raw("(SELECT name FROM companies WHERE id = Documento_Contable.Id_Empresa) AS Empresa"),
            'Documento_Contable.Concepto',
            DB::raw("GROUP_CONCAT(Cuenta_Documento_Contable.Cheque SEPARATOR ' | ') AS Cheques"),
            DB::raw("SUM(Cuenta_Documento_Contable.Debito) AS Total_Debe_PCGA"),
            DB::raw("SUM(Cuenta_Documento_Contable.Credito) AS Total_Haber_PCGA"),
            DB::raw("SUM(Cuenta_Documento_Contable.Deb_Niif) AS Total_Debe_NIIF"),
            DB::raw("SUM(Cuenta_Documento_Contable.Cred_Niif) AS Total_Haber_NIIF"),
            DB::raw("(SELECT CONCAT_WS(' ', first_name, first_surname) FROM people WHERE identifier = Documento_Contable.Identificacion_Funcionario) AS Funcionario")
        )
            ->join('Documento_Contable', 'Documento_Contable.Id_Documento_Contable', '=', 'Cuenta_Documento_Contable.Id_Documento_Contable')
            ->where('Documento_Contable.Tipo', 'Nota Contable')
            ->groupBy('Documento_Contable.Id_Documento_Contable');


        if (request()->has('tercero') && request('tercero') != '') {
            $tercero = request('tercero');
            $query->havingRaw("Beneficiario LIKE '$tercero%' OR Tercero LIKE '$tercero%'");
        }

        if (request()->has('empresa') && request('empresa') != '') {
            $empresaId = request('empresa');
            $query->where('Documento_Contable.Id_Empresa', $empresaId);
        }

        if (request()->has('codigo') && request('codigo') != '') {
            $codigo = request('codigo');
            $query->havingRaw("Documento_Contable.Codigo LIKE '%$codigo%'");
        }


        if (
            request()->has('start_date') && request()->has('end_date') &&
            request('start_date') != '' && request('end_date') != ''
        ) {
            $start_date = date('Y-m-d', strtotime(str_replace('/', '-', request('start_date'))));
            $end_date = date('Y-m-d', strtotime(str_replace('/', '-', request('end_date'))));
            $query->whereBetween('Documento_Contable.Fecha_Documento', [$start_date, $end_date]);
        }

        if (request()->has('estado') && request('estado') != '') {
            $estado = request('estado');
            $query->havingRaw("Documento_Contable.Estado = '$estado'");
        }

        $resultado = $query->paginate(request()->get('pageSize', 10), ['*'], 'page', request()->get('page', 1));

        return $this->success($resultado);
    }

    private function getCompany()
    {
        return Person::find(Auth()->user()->person_id)->company_worked_id;
    }

    public function getCodigo()
    {
        $mes = isset($_REQUEST['Fecha']) && $_REQUEST['Fecha'] != '' ? date('m', strtotime($_REQUEST['Fecha'])) : date('m');
        $anio = isset($_REQUEST['Fecha']) && $_REQUEST['Fecha'] != '' ? date('Y', strtotime($_REQUEST['Fecha'])) : date('Y');

        $consecutivo = obtenerProximoConsecutivo::obtener('Nota', $this->getCompany());

        return response()->json([
            "consecutivo" => $consecutivo
        ]);
    }


    public function nitBuscar()
    {
        $clientes = ThirdParty::select('id as ID', DB::raw("IFNULL(CONCAT_WS(' ', id, '-', first_name, second_name, first_surname, second_surname), CONCAT(id, ' - ', social_reason)) AS Nombre"), DB::raw("'Cliente' as Tipo"))
            ->where('state', 'activo')
            ->where('is_client', true);

        $proveedores = ThirdParty::select('id as ID', DB::raw("IFNULL(CONCAT_WS(' ', id, '-', first_name, second_name, first_surname, second_surname), CONCAT(id, ' - ', social_reason)) AS Nombre"), DB::raw("'Proveedor' as Tipo"))
            ->where('state', 'activo')
            ->where('is_supplier', true);

        $funcionarios = Person::select('id as ID', DB::raw("CONCAT(id, ' - ', first_name, ' ', first_surname) as Nombre"), DB::raw("'Funcionario' as Tipo"));

        $proveedorbucar = $clientes->union($proveedores)->union($funcionarios)->get();

        return response()->json($proveedorbucar);
    }





    public function subirFacturas()
    {
        $http_response = new HttpResponse();


        if (!empty($_FILES['archivo']['name'])) { // Archivo de la archivo de Entrega.
            $posicion1 = strrpos($_FILES['archivo']['name'], '.') + 1;
            $extension1 = substr($_FILES['archivo']['name'], $posicion1);
            $extension1 = strtolower($extension1);
            $_filename1 = uniqid() . "." . $extension1;
            $_file1 = app_path() . "ARCHIVOS/COMPROBANTES/" . $_filename1;

            //$subido1 = move_uploaded_file($_FILES['archivo']['tmp_name'], $_file1);
        }

        $inputFileName = app_path() . "ARCHIVOS/COMPROBANTES/" . $_filename1;

        try {

            $inputFileType = Excel::identify($inputFileName); // PHPExcel_IOFactory::identify($inputFileName);
            $objReader = Excel::createReader($inputFileType); //PHPExcel_IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
        } catch (Exception $e) {
            die('Error loading file "' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": ' . $e->getMessage());
        }
        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = 'I';

        $facturas = [];
        $i = -1;
        for ($row = 1; $row <= $highestRow; $row++) {
            $i++;
            //  Read a row of data into an array
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row);
            $facturas[$i] = $rowData;
        }
        $facturas_no_encontradas = [];
        $retenciones = [];
        $descuentos = [];
        $ajustes = [];
        $fact = [];
        $f = 0;
        $a = 0;
        $d = 0;
        $i = 0;
        $r = 0;
        $x = 0;
        $y = 0;


        foreach ($facturas as $value) {

            foreach ($value as $key => $item) {
                if ($item[0] != '' && $item[1] != '') { // Si el plan y el nit son diferentes de vacío consultamos.
                    $valido = true;


                    $datosTercero = $this->getDatosTercero($item[1]);

                    $plan_cuentas = $this->getDatosPlanCuenta($item[0]);
                    # $id_plan_cuentas = getIdPlanCuenta($item[0]);


                    $centrocosto = $this->getDetalleCentroCosto($item[2]);
                    //$id_centro_costo = getIdCentroCosto($item[2]);

                    if (!$datosTercero || !$plan_cuentas || !$centrocosto) {
                        # code...
                        $valido = false;
                    }

                    $factura = [
                        #"Id_Plan_Cuentas" => $id_plan_cuentas,
                        "Id_Plan_Cuentas" => $plan_cuentas['Id_Plan_Cuentas'],
                        #"Cuenta" => getDatosPlanCuenta($item[0]),
                        "Cuenta" => $plan_cuentas,
                        "Nit_Cuenta" => $item[1],
                        "Nit" => $datosTercero,
                        "Tipo_Nit" => $datosTercero['Tipo'],
                        #"Id_Centro_Costo" =>$id_centro_costo,
                        "Id_Centro_Costo" => $centrocosto['Id_Centro_Costo'],
                        #"Centro_Costo" => getDetalleCentroCosto($item[2]),
                        "Centro_Costo" => $centrocosto,
                        "Documento" => $item[3],
                        "Concepto" => $item[4],
                        "Base" => '0',
                        "Debito" => $item[5] != '' ? str_replace(",", ".", $item[5]) : '0',
                        "Credito" => $item[6] != '' ? str_replace(",", ".", $item[6]) : '0',
                        "Deb_Niif" => $item[7] != '' ? str_replace(",", ".", $item[7]) : '0',
                        "Cred_Niif" => $item[8] != '' ? str_replace(",", ".", $item[8]) : '0',
                        "Valido" => $valido
                    ];

                    $fact[] = $factura;
                }
            }
            if ($x == 200) {

                $logFile = fopen('prueba.txt', 'w') or die("Error creando archivo");
                ;
                fwrite($logFile, $y);
                $y++;
                sleep(5);
                $x = 0;
            }

            $x++;
        }

        $file = app_path() . "ARCHIVOS/COMPROBANTES/" . $_filename1;
        unlink($file);
        $resultado['Facturas'] = $fact;

        return response()->json($resultado);
    }

    function getIdPlanCuenta($codigo)
    {
        $query = "SELECT P.Id_Plan_Cuentas FROM Plan_Cuentas P WHERE P.Codigo='$codigo'";

        $oCon = new consulta();
        $oCon->setQuery($query);
        $resultado = $oCon->getData();
        unset($oCon);

        return $resultado ? $resultado['Id_Plan_Cuentas'] : '0';
    }

    function getDatosPlanCuenta($codigo)
    {
        $query = 'SELECT PC.Id_Plan_Cuentas, PC.Id_Plan_Cuentas AS Id,
        PC.Codigo, PC.Codigo AS Codigo_Cuenta,/*  CONCAT(PC.Nombre," - ",PC.Codigo) as Codigo,  */
        CONCAT(PC.Codigo," - ",PC.Nombre) as Nombre, PC.Centro_Costo
        FROM Plan_Cuentas PC WHERE PC.Codigo = "' . $codigo . '"';

        $oCon = new consulta();
        $oCon->setQuery($query);
        $resultado = $oCon->getData();
        unset($oCon);

        return $resultado ? $resultado : [];
    }

    function getDatosTercero($nit)
    {
        $query = 'SELECT r.* FROM (
            (
            SELECT C.Id_Cliente AS ID, IF(Nombre IS NULL OR Nombre = "", CONCAT_WS(" ", C.Id_Cliente,"-",Primer_Nombre, Segundo_Nombre, Primer_Apellido, Segundo_Apellido),
             CONCAT(C.Id_Cliente, " - ", C.Nombre)) AS Nombre, "Cliente" AS Tipo FROM Cliente C WHERE C.Estado != "Inactivo" AND C.Id_Cliente = ' . $nit . ' )

            UNION (SELECT P.Id_Proveedor AS ID, IF(P.Nombre = "" OR P.Nombre IS NULL,
                CONCAT_WS(" ",P.Id_Proveedor,"-",P.Primer_Nombre,P.Segundo_Nombre,P.Primer_Apellido,P.Segundo_Apellido),CONCAT(P.Id_Proveedor, " - ", P.Nombre)) AS Nombre,
                "Proveedor" AS Tipo FROM Proveedor P
                WHERE  P.Id_Proveedor = ' . $nit . '
                )

            UNION (SELECT F.Identificacion_Funcionario AS ID, CONCAT(F.Identificacion_Funcionario, " - ", F.Nombres," ", F.Apellidos) AS Nombre,
                "Funcionario" AS Tipo FROM Funcionario F
                WHERE  F.Identificacion_Funcionario  = ' . $nit . '
                )
            UNION (SELECT CC.Nit AS ID, CONCAT(CC.Nit, " - ", CC.Nombre) AS Nombre, "Caja_Compensacion" AS Tipo FROM Caja_Compensacion CC
                WHERE CC.Nit IS NOT NULL AND
                CC.Nit  = ' . $nit . '
                )
            )   r ';

        $oCon = new consulta();
        $oCon->setQuery($query);
        $resultado = $oCon->getData();
        unset($oCon);

        return $resultado ? $resultado : [];
    }

    function getIdCentroCosto($codigo_centro_costo)
    {

        $id_centro_costo = '0';

        if ($codigo_centro_costo != '') {
            $query = "SELECT Id_Centro_Costo FROM Centro_Costo WHERE Codigo LIKE '%$codigo_centro_costo' LIMIT 1";

            $oCon = new consulta();
            $oCon->setQuery($query);
            $resultado = $oCon->getData();

            if ($resultado) {
                $id_centro_costo = $resultado['Id_Centro_Costo'];
            }
        }

        return $id_centro_costo;
    }

    function getDetalleCentroCosto($codigo_centro_costo)
    {

        $res = [];

        if ($codigo_centro_costo != '') {
            $query = 'SELECT CONCAT(Codigo, " - ", Nombre) AS Nombre, Id_Centro_Costo FROM Centro_Costo WHERE Movimiento = "Si" AND Estado = "Activo" AND Codigo LIKE "%' . $codigo_centro_costo . '" LIMIT 1';

            $oCon = new consulta();
            $oCon->setQuery($query);
            $centrocosto = $oCon->getData();
            unset($oCon);

            if ($centrocosto) {
                $res = $centrocosto;
            }
        }

        return $res;
    }
    public function descargarPdf(Request $request)
    {
        $id = $request->input('id', '');
        $tipo = $request->input('tipo', '');
        $id_funcionario_imprime = $request->input('id_funcionario_elabora', '');


        $data = DocumentoContable::select('Documento_Contable.*')
            ->selectRaw('(CASE
                Tipo_Beneficiario
                WHEN "Cliente" THEN (SELECT IFNULL(CONCAT_WS(" ", first_name, second_name, first_surname, second_surname), social_reason) FROM third_parties WHERE id = Documento_Contable.Beneficiario AND is_client = 1 LIMIT 1)
                WHEN "Proveedor" THEN (SELECT IFNULL(CONCAT_WS(" ", first_name, second_name, first_surname, second_surname), social_reason) FROM third_parties WHERE id = Documento_Contable.Beneficiario AND is_supplier = 1 LIMIT 1)
                WHEN "people" THEN (SELECT CONCAT_WS(" ", first_name, first_surname) FROM people WHERE id = Documento_Contable.Beneficiario LIMIT 1)
            END) AS Tercero')
            ->selectRaw('(SELECT name FROM companies LIMIT 1) AS Empresa')
            ->selectRaw('(SELECT tin FROM companies LIMIT 1) AS document_number')
            ->selectRaw('(SELECT document_type FROM companies LIMIT 1) AS document_type')
            ->selectRaw('(SELECT IFNULL(Nombre,"Sin Centro Costo") FROM Centro_Costo WHERE Id_Centro_Costo = Documento_Contable.Id_Centro_Costo LIMIT 1) AS Centro_Costo')
            ->where('Id_Documento_Contable', $id)
            ->first();


        $cuentas = CuentaDocumentoContable::where('Id_Documento_Contable', $id)
            ->join('Plan_Cuentas', 'Cuenta_Documento_Contable.Id_Plan_Cuenta', '=', 'Plan_Cuentas.Id_Plan_Cuentas')
            ->leftJoin('Centro_Costo', 'Cuenta_Documento_Contable.Id_Centro_Costo', '=', 'Centro_Costo.Id_Centro_Costo')
            ->select(
                'Cuenta_Documento_Contable.*',
                'Plan_Cuentas.Codigo',
                'Plan_Cuentas.Nombre AS Cuenta',
                'Plan_Cuentas.Nombre_Niif AS Cuenta_Niif',
                'Plan_Cuentas.Codigo_Niif',
                'Plan_Cuentas.Documento AS Documento_niif',
                'Cuenta_Documento_Contable.Concepto',
                'Cuenta_Documento_Contable.Documento',
                'Cuenta_Documento_Contable.Nit',
                'Centro_Costo.Nombre AS Nombre_Centro_Costo'
            )
            ->get();



        $elabora = Person::where('id', $id_funcionario_imprime)
            ->value(DB::raw("IFNULL(CONCAT_WS(' ', first_name, first_surname), 'Nombre no disponible')"));

        $header = (object) [
            'Titulo' => 'CONT. PCGA',
            'Codigo' => $data->code ?? '',
            'Fecha' => $data->created_at,
            'CodigoFormato' => $data->format_code ?? '',
        ];

        $pdf = Pdf::loadView('pdf.CONT_PCGA', [
            'data' => $data,
            'cuentas' => $cuentas,
            'datosCabecera' => $header,
            'elabora' => $elabora,
            'tipo' => $tipo

        ]);

        return $pdf->download("CONT_PCGA");
    }



    public function guardarNota(Request $request)
    {

        $datos = request()->input('Datos', false);
        $cuentas_contables = request()->input('Cuentas_Contables', false);

        $datos = json_decode($datos, true);
        $cuentas_contables = json_decode($cuentas_contables, true);

        $mes = isset($datos['Fecha_Documento']) ? date('m', strtotime($datos['Fecha_Documento'])) : date('m');
        $anio = isset($datos['Fecha_Documento']) ? date('Y', strtotime($datos['Fecha_Documento'])) : date('Y');

        $cod = ObtenerProximoConsecutivo::generarConsecutivo('Nota', $mes, $anio);
        $datos['Codigo'] = $cod;

        $oItem = new DocumentoContable();
        $oItem->fill($datos);
        $oItem->save();
        $id_nota_contable = $oItem->Id_Documento_Contable;
        unset($oItem);

        $cuentas_contables = request()->input('Cuentas_Contables', false);
        $cuentas_contables = json_decode($cuentas_contables, true);
        if ($cuentas_contables === null) {
            $cuentas_contables = [];
        }

        if (!empty($cuentas_contables)) {
            unset($cuentas_contables[count($cuentas_contables) - 1]);
        }

        $x = 0;
        $y = 0;
        $docs = '';
        foreach ($cuentas_contables as $cuenta) {
            $oItem = new CuentaDocumentoContable();
            $oItem->Id_Documento_Contable = $id_nota_contable;
            $oItem->Id_Plan_Cuenta = $cuenta['Id_Plan_Cuentas'] != '' ? $cuenta['Id_Plan_Cuentas'] : '0';
            $oItem->Nit = $cuenta['Nit_Cuenta'];
            $oItem->Tipo_Nit = $cuenta['Tipo_Nit'];
            $oItem->Id_Centro_Costo = isset($cuenta['Id_Centro_Costo']) && $cuenta['Id_Centro_Costo'] != '' ? $cuenta['Id_Centro_Costo'] : '0';
            $oItem->Documento = $cuenta['Documento'];
            $oItem->Concepto = $cuenta['Concepto'];
            $base = $cuenta['Base'] != "" ? $cuenta['Base'] : 0;
            $oItem->Base = number_format($base, 2, ".", "");
            $oItem->Debito = number_format($cuenta['Debito'], 2, ".", "");
            $oItem->Credito = number_format($cuenta['Credito'], 2, ".", "");
            $oItem->Deb_Niif = number_format($cuenta['Deb_Niif'], 2, ".", "");
            $oItem->Cred_Niif = number_format($cuenta['Cred_Niif'], 2, ".", "");
            $oItem->save();

            $oItem = new MovimientoContable();
            $oItem->Id_Plan_Cuenta = $cuenta['Id_Plan_Cuentas'] != '' ? $cuenta['Id_Plan_Cuentas'] : '0';
            $oItem->Id_Modulo = 5;
            $oItem->Id_Registro_Modulo = $id_nota_contable;
            $oItem->Fecha_Movimiento = $datos['Fecha_Documento'];
            $oItem->Debe = number_format($cuenta['Debito'], 2, ".", "");
            $oItem->Debe_Niif = number_format($cuenta['Deb_Niif'], 2, ".", "");
            $oItem->Haber = number_format($cuenta['Credito'], 2, ".", "");
            $oItem->Haber_Niif = number_format($cuenta['Cred_Niif'], 2, ".", "");
            $oItem->Nit = $cuenta['Nit_Cuenta'];
            $oItem->Tipo_Nit = $cuenta['Tipo_Nit'];
            $oItem->Documento = $cuenta['Documento'];
            $oItem->Id_Centro_Costo = isset($cuenta['Id_Centro_Costo']) && $cuenta['Id_Centro_Costo'] != '' ? $cuenta['Id_Centro_Costo'] : '0';
            $oItem->Numero_Comprobante = $cod;
            $oItem->Detalles = $cuenta['Concepto'];
            $oItem->save();

            if ($cuenta['Id_Plan_Cuentas'] == 85 || $cuenta['Id_Plan_Cuentas'] == 272) {
                $docs .= '"' . $cuenta['Documento'] . '",';
            }

            if ($x == 200) {
                $logFile = fopen('prueba.txt', 'w') or die("Error creando archivo");
                ;
                fwrite($logFile, $docs);
                $y++;
                sleep(5);
                $x = 0;
            }

            $x++;
        }

        if (isset($datos['Id_Borrador']) && $datos['Id_Borrador'] != '') {
            funciones::eliminarBorradorContable($datos['Id_Borrador']);
        }

        if ($id_nota_contable) {

            if (isset($datos['Tipo'])) {
                $resultado['mensaje'] = "Se ha registrado un comprobante de " . $datos['Tipo'] . " satisfactoriamente";
            } else {
                // Manejo del caso en que la clave 'Tipo' no esté definida
                $resultado['mensaje'] = "La clave 'Tipo' no está definida en los datos enviados.";
            }
            $resultado['tipo'] = "success";
            $resultado['titulo'] = "Operación exitosa!";
            $resultado['id'] = $id_nota_contable;
        } else {
            $resultado['mensaje'] = "Ha ocurrido un error de conexión, comunicarse con el soporte técnico.";
            $resultado['tipo'] = "error";
        }

        return response()->json($resultado);

    }
}
