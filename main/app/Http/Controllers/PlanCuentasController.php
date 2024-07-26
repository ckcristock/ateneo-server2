<?php

namespace App\Http\Controllers;

use App\Exports\PlanCuentasExport;
use App\Models\PlanCuentas;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use App\Http\Services\PaginacionData;
use App\Http\Services\HttpResponse;
use App\Http\Services\consulta;
use App\Http\Services\complex;
use App\Http\Services\QueryBaseDatos;
use App\Imports\AccountPlansImport;
use App\Imports\InitialBalanceImport;
use App\Models\AccountPlanBalance;
use App\Models\Person;
use App\Models\PrettyCash;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;
use App\Models\Bank;

class PlanCuentasController extends Controller
{

    use ApiResponser;

    private function getCompany()
    {
        return Person::find(Auth()->user()->person_id)->company_worked_id;
    }

    public function importCommercialPuc()
    {
        $file_path = 'puc-comercial.xlsx';
        $full_path = storage_path('app/public/' . $file_path);
        if (AccountPlanBalance::count() && AccountPlanBalance::where('balance', '<>', 0)->count()) {
            return $this->success(
                response()->json([
                    'icon' => 'error',
                    'title' => 'Error',
                    'text' => 'No podemos hacer esto porque ya hay saldos iniciales.',
                    'timer' => 0
                ])
            );
        } else {
            $companyId = $this->getCompany();
            $deletedAccountPlanIds = PlanCuentas::where('company_id', $companyId)->pluck('Id_Plan_Cuentas');
            AccountPlanBalance::whereIn('account_plan_id', $deletedAccountPlanIds)->delete();
            PlanCuentas::where('company_id', $companyId)->delete();
            Excel::import(new AccountPlansImport, $full_path);
            return $this->success(
                response()->json([
                    'icon' => 'success',
                    'title' => 'Operación exitosa',
                    'text' => 'PUC comercial cargado correctamente.',
                    'timer' => 1000
                ])
            );
        }
    }
    public function setTipoCierre()
    {
        $id_plan_cuenta = isset($_REQUEST['id_plan_cuenta']) ? $_REQUEST['id_plan_cuenta'] : false;

        # MENSUAL O ANUAL
        $tipo_cierre = isset($_REQUEST['tipo_cierre']) ? $_REQUEST['tipo_cierre'] : false;

        #Costos - Gastos - Ingresos - Sin Asignar
        $valor_actualizar = isset($_REQUEST['valor_actualizar']) && $_REQUEST['valor_actualizar'] != '' ? $_REQUEST['valor_actualizar'] : 'Sin Asignar';

        $input = 'Tipo_Cierre_' . $tipo_cierre;
        $oItem = new complex('Plan_Cuentas', 'Id_Plan_Cuentas', $id_plan_cuenta);
        $oItem->$input = $valor_actualizar;
        $oItem->save();
    }

    public function balancePruebasListaCuentas()
    {
        $query = 'SELECT PC.Id_Plan_Cuentas, CONCAT(PC.Codigo," - ",PC.Nombre) as Codigo, PC.Centro_Costo, PC.Porcentaje, PC.Codigo AS Codigo_Centro
            FROM Plan_Cuentas PC
            #WHERE PC.Movimiento = "S"
            ORDER BY PC.Codigo';

        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->setTipo('Multiple');
        $resultado['Activo'] = $oCon->getData();
        unset($oCon);

        return response()->json($resultado);
    }

    function fecha($str)
    {
        $parts = explode(" ", $str);
        $date = explode("-", $parts[0]);
        return $date[2] . "/" . $date[1] . "/" . $date[0];
    }

    public function balancePruebasPdf()
    {
        $tipo = (isset($_REQUEST['tipo']) ? $_REQUEST['tipo'] : '');
        $fecha_ini = (isset($_REQUEST['fecha_ini']) ? $_REQUEST['fecha_ini'] : '');
        $fecha_fin = (isset($_REQUEST['fecha_fin']) ? $_REQUEST['fecha_fin'] : '');
        $tipo_reporte = (isset($_REQUEST['tipo_reporte']) ? $_REQUEST['tipo_reporte'] : '');
        $nivel_reporte = (isset($_REQUEST['nivel']) ? $_REQUEST['nivel'] : '');
        $cta_ini = (isset($_REQUEST['cta_ini']) ? $_REQUEST['cta_ini'] : '');
        $cta_fin = (isset($_REQUEST['cta_fin']) ? $_REQUEST['cta_fin'] : '');
        $ultimo_dia_mes = $this->getUltimoDiaMes($fecha_ini);

        /* FUNCIONES BASICAS */

        /* FIN FUNCIONES BASICAS*/

        /* DATOS GENERALES DE CABECERAS Y CONFIGURACION */
        $oItem = new complex('Configuracion', "Id_Configuracion", 1);
        $config = $oItem->getData();
        unset($oItem);
        /* FIN DATOS GENERALES DE CABECERAS Y CONFIGURACION */

        $movimientos = $this->getMovimientosCuenta($fecha_ini, $fecha_fin);

        $totales = [
            "saldo_anterior" => 0,
            "debito" => 0,
            "credito" => 0,
            "nuevo_saldo" => 0,
            "clases" => []
        ];

        ob_start(); // Se Inicializa el gestor de PDF

        /* HOJA DE ESTILO PARA PDF*/
        $style = '<style>
        .page-content{
        width:750px;
        }
        .row{
        display:inlinie-block;
        width:750px;
        }
        .td-header{
            font-size:15px;
            line-height: 20px;
        }
        .titular{
            font-size: 11px;
            text-transform: uppercase;
            margin-bottom: 0;
          }
        </style>';
        /* FIN HOJA DE ESTILO PARA PDF*/

        /* HAGO UN SWITCH PARA TODOS LOS MODULOS QUE PUEDEN GENERAR PDF */

        $tipo_balance = strtoupper($tipo);

        $codigos = '
                    <h4 style="margin:5px 0 0 0;font-size:19px;line-height:22px;">BALANCE DE PRUEBA</h4>
                    <h4 style="margin:5px 0 0 0;font-size:19px;line-height:22px;">' . $tipo_balance . '</h4>
                    <h4 style="margin:5px 0 0 0;font-size:19px;line-height:22px;">' . strtoupper($tipo_reporte) . '</h4>
                    <h5 style="margin:5px 0 0 0;font-size:16px;line-height:16px;">Fecha Ini. ' . $this->fecha($fecha_ini) . '</h5>
                    <h5 style="margin:5px 0 0 0;font-size:16px;line-height:16px;">Fecha Fin. ' . $this->fecha($fecha_fin) . '</h5>
                ';

        $contenido = '<table style="font-size:10px;margin-top:10px;" cellpadding="0" cellspacing="0">
            <tr>
                <td style="width:50px;font-weight:bold;text-align:center;background:#cecece;;border:1px solid #cccccc;">
                    Cuenta
                </td>
                <td style="width:250px;font-weight:bold;background:#cecece;text-align:center;border:1px solid #cccccc;">
                   Nombre Cuenta
                </td>
                <td style="width:100px;font-weight:bold;background:#cecece;text-align:center;border:1px solid #cccccc;">
                   Saldo Anterior
                </td>
                <td style="width:100px;font-weight:bold;background:#cecece;text-align:center;border:1px solid #cccccc;">
                    Debitos
                </td>
                <td style="width:100px;font-weight:bold;background:#cecece;text-align:center;border:1px solid #cccccc;">
                    Creditos
                </td>
                <td style="width:100px;font-weight:bold;background:#cecece;text-align:center;border:1px solid #cccccc;">
                    Nuevo Saldo
                </td>
            </tr>';

        $totalCant = 0;
        $totalCosto = 0;
        $column_1 = 'Codigo';
        $column_2 = 'Codigo_Niif';

        $column = $tipo_reporte == 'Pcga' ? 'Codigo' : 'Codigo_Niif';

        if ($tipo == 'General') {

            $query = "SELECT

                PC.Codigo,
                PC.Nombre,
                Codigo_Niif,
                Nombre_Niif,
                PC.Naturaleza,
                (SELECT IFNULL(SUM(Debito_PCGA),0) FROM Balance_Inicial_Contabilidad WHERE Fecha = '$ultimo_dia_mes' AND Codigo_Cuenta LIKE CONCAT(PC.$column_1,'%')) AS Debito_PCGA,
                (SELECT IFNULL(SUM(Credito_PCGA),0) FROM Balance_Inicial_Contabilidad WHERE Fecha = '$ultimo_dia_mes' AND Codigo_Cuenta LIKE CONCAT(PC.$column_1,'%')) AS Credito_PCGA,
                (SELECT IFNULL(SUM(Debito_NIIF),0) FROM Balance_Inicial_Contabilidad WHERE Fecha = '$ultimo_dia_mes' AND Codigo_Cuenta_Niif LIKE CONCAT(PC.$column_2,'%')) AS Debito_NIIF,
                (SELECT IFNULL(SUM(Credito_NIIF),0) FROM Balance_Inicial_Contabilidad WHERE Fecha = '$ultimo_dia_mes' AND Codigo_Cuenta_Niif LIKE CONCAT(PC.$column_2,'%')) AS Credito_NIIF,
                PC.Estado,
                PC.Movimiento,
                PC.Tipo_P
            FROM
                Plan_Cuentas PC
                    LEFT JOIN
                 (SELECT * FROM Balance_Inicial_Contabilidad WHERE Fecha = '$ultimo_dia_mes') BIC ON BIC.Id_Plan_Cuentas = PC.Id_Plan_Cuentas
                 " . $this->getStrCondiciones() . "
                 GROUP BY PC.Id_Plan_Cuentas
            HAVING Estado = 'ACTIVO' OR (Estado = 'INACTIVO' AND (Debito_PCGA > 0 OR Credito_PCGA > 0 OR Debito_NIIF > 0 OR Credito_NIIF > 0))
            ORDER BY PC.$column";
            #echo $query;exit;
            $oCon = new consulta();
            $oCon->setQuery($query);
            $oCon->setTipo('Multiple');
            $balance = $oCon->getData();
            unset($oCon);
            /* echo "<pre>";
                var_dump($balance);
                echo "</pre>";
                exit; */

            $acum_saldo_anterior = 0;
            $acum_debito = 0;
            $acum_credito = 0;
            $acum_nuevo_saldo = 0;

            foreach ($balance as $i => $value) {

                $codigo = $tipo_reporte == 'Pcga' ? $value['Codigo'] : $value['Codigo_Niif'];
                $nombre_cta = $tipo_reporte == 'Pcga' ? $value['Nombre'] : $value['Nombre_Niif'];

                $saldo_anterior = $this->obtenerSaldoAnterior($value['Naturaleza'], $balance, $i, $tipo_reporte);
                $debito = $this->calcularDebito($codigo, $value['Tipo_P'], $movimientos);
                $credito = $this->calcularCredito($codigo, $value['Tipo_P'], $movimientos);
                $nuevo_saldo = $this->calcularNuevoSaldo($value['Naturaleza'], $saldo_anterior, $debito, $credito);

                if ($saldo_anterior != 0 || $debito != 0 || $credito != 0 || $nuevo_saldo != 0) {
                    $contenido .= '
                        <tr>
                            <td style="padding:4px;text-align:left;border:1px solid #cccccc;">
                                ' . $codigo . '
                            </td>
                            <td style="width:250px;padding:4px;text-align:left;border:1px solid #cccccc;">
                                ' . $nombre_cta . '
                            </td>
                            <td style="padding:4px;text-align:right;border:1px solid #cccccc;">
                                $ ' . number_format($saldo_anterior, 2, ",", ".") . '
                            </td>
                            <td style="padding:4px;text-align:right;border:1px solid #cccccc;">
                            $ ' . number_format($debito, 2, ",", ".") . '
                            </td>
                            <td style="padding:4px;text-align:right;border:1px solid #cccccc;">
                            $ ' . number_format($credito, 2, ",", ".") . '
                            </td>
                            <td style="padding:4px;text-align:right;border:1px solid #cccccc;">
                            $ ' . number_format($nuevo_saldo, 2, ",", ".") . '
                            </td>
                        </tr>';

                    /* $acum_saldo_anterior += $saldo_anterior;
                        $acum_debito += $debito;
                        $acum_credito += $credito;
                        $acum_nuevo_saldo += $nuevo_saldo; */

                    if ($value['Tipo_P'] == 'CLASE') {
                        $totales['clases'][$value['Codigo']]['saldo_anterior'] = $saldo_anterior;
                        $totales['clases'][$value['Codigo']]['nuevo_saldo'] = $nuevo_saldo;
                        //La formula para estos campos: 1+2+3+4+5+6+8 (A nivel de cuentas)
                        $totales['debito'] += $debito;
                        $totales['credito'] += $credito;
                    }
                }
            }

            $totales['saldo_anterior'] = $this->getTotal($totales, 'saldo_anterior');

            $totales['nuevo_saldo'] = $this->getTotal($totales, 'nuevo_saldo');


            $contenido .= '
                <tr>

                        <td colspan="2" style="background:#cecece;padding:4px;text-align:left;border:1px solid #cccccc;">
                            Total
                        </td>
                        <td style="background:#cecece;padding:4px;text-align:right;border:1px solid #cccccc;">
                            $ ' . number_format($totales['saldo_anterior'], 2, ",", ".") . '
                        </td>
                        <td style="background:#cecece;padding:4px;text-align:right;border:1px solid #cccccc;">
                        $ ' . number_format($totales['debito'], 2, ",", ".") . '
                        </td>
                        <td style="background:#cecece;padding:4px;text-align:right;border:1px solid #cccccc;">
                        $ ' . number_format($totales['credito'], 2, ",", ".") . '
                        </td>
                        <td style="background:#cecece;padding:4px;text-align:right;border:1px solid #cccccc;">
                        $ ' . number_format($totales['nuevo_saldo'], 2, ",", ".") . '
                        </td>
                    </tr>';
        } elseif ($tipo == 'Nits') {

            $query = "SELECT
                PC.Id_Plan_Cuentas,
                PC.Codigo,
                PC.Nombre,
                Codigo_Niif,
                Nombre_Niif,
                PC.Naturaleza,
                IFNULL(SUM(BIC.Debito_PCGA), (SELECT IFNULL(SUM(Debito_PCGA),0) FROM Balance_Inicial_Contabilidad WHERE Fecha = '$ultimo_dia_mes' AND Codigo_Cuenta LIKE CONCAT(PC.$column_1,'%'))) AS Debito_PCGA,
                IFNULL(SUM(BIC.Credito_PCGA), (SELECT IFNULL(SUM(Credito_PCGA),0) FROM Balance_Inicial_Contabilidad WHERE Fecha = '$ultimo_dia_mes' AND Codigo_Cuenta LIKE CONCAT(PC.$column_1,'%'))) AS Credito_PCGA,
                IFNULL(SUM(BIC.Debito_NIIF), (SELECT IFNULL(SUM(Debito_NIIF),0) FROM Balance_Inicial_Contabilidad WHERE Fecha = '$ultimo_dia_mes' AND Codigo_Cuenta LIKE CONCAT(PC.$column_2,'%'))) AS Debito_NIIF,
                IFNULL(SUM(BIC.Credito_NIIF), (SELECT IFNULL(SUM(Credito_NIIF),0) FROM Balance_Inicial_Contabilidad WHERE Fecha = '$ultimo_dia_mes' AND Codigo_Cuenta LIKE CONCAT(PC.$column_2,'%'))) AS Credito_NIIF,
                PC.Estado,
                PC.Movimiento,
                PC.Tipo_P
            FROM
                Plan_Cuentas PC
                    LEFT JOIN
                 (SELECT * FROM Balance_Inicial_Contabilidad WHERE Fecha = '$ultimo_dia_mes') BIC ON BIC.Id_Plan_Cuentas = PC.Id_Plan_Cuentas
                 " . $this->getStrCondiciones() . "
                 GROUP BY PC.Id_Plan_Cuentas
            HAVING Estado = 'ACTIVO' OR (Estado = 'INACTIVO' AND (Debito_PCGA > 0 OR Credito_PCGA > 0 OR Debito_NIIF > 0 OR Credito_NIIF > 0))
            ORDER BY PC.$column";


            $oCon = new consulta();
            $oCon->setQuery($query);
            $oCon->setTipo('Multiple');
            $balance = $oCon->getData();
            unset($oCon);

            foreach ($balance as $i => $value) {
                $balance[$i]['nits'] = $this->nitsPorCuentasContables($value['Id_Plan_Cuentas']);
            }

            foreach ($balance as $i => $value) {

                $codigo = $tipo_reporte == 'Pcga' ? $value['Codigo'] : $value['Codigo_Niif'];
                $nombre_cta = $tipo_reporte == 'Pcga' ? $value['Nombre'] : $value['Nombre_Niif'];

                $saldo_anterior = $this->obtenerSaldoAnterior($value['Naturaleza'], $balance, $i, $tipo_reporte);
                $debito = $this->calcularDebito($codigo, $value['Tipo_P'], $movimientos);
                $credito = $this->calcularCredito($codigo, $value['Tipo_P'], $movimientos);
                $nuevo_saldo = $this->calcularNuevoSaldo($value['Naturaleza'], $saldo_anterior, $debito, $credito);

                if ($saldo_anterior != 0 || $debito != 0 || $credito != 0 || $nuevo_saldo != 0) {
                    $contenido .= '
                        <tr>
                            <td style="padding:4px;text-align:left;border:1px solid #cccccc;">
                                ' . $codigo . '
                            </td>
                            <td style="width:250px;padding:4px;text-align:left;border:1px solid #cccccc;">
                                ' . $nombre_cta . '
                            </td>
                            <td style="padding:4px;text-align:right;border:1px solid #cccccc;">
                                $ ' . number_format($saldo_anterior, 2, ",", ".") . '
                            </td>
                            <td style="padding:4px;text-align:right;border:1px solid #cccccc;">
                            $ ' . number_format($debito, 2, ",", ".") . '
                            </td>
                            <td style="padding:4px;text-align:right;border:1px solid #cccccc;">
                            $ ' . number_format($credito, 2, ",", ".") . '
                            </td>
                            <td style="padding:4px;text-align:right;border:1px solid #cccccc;">
                            $ ' . number_format($nuevo_saldo, 2, ",", ".") . '
                            </td>
                        </tr>';

                    /* $acum_saldo_anterior += $saldo_anterior;
                        $acum_debito += $debito;
                        $acum_credito += $credito;
                        $acum_nuevo_saldo += $nuevo_saldo; */

                    if ($value['Tipo_P'] == 'CLASE') {
                        $totales['clases'][$value['Codigo']]['saldo_anterior'] = $saldo_anterior;
                        $totales['clases'][$value['Codigo']]['nuevo_saldo'] = $nuevo_saldo;
                        //La formula para estos campos: 1+2+3+4+5+6+8 (A nivel de cuentas)
                        $totales['debito'] += $debito;
                        $totales['credito'] += $credito;
                    }

                    // $nits = nitsPorCuentasContables($value['Id_Plan_Cuentas']);
                }

                $nits = $value['nits'];

                foreach ($nits as $j => $nit) {

                    $saldo_anterior = $this->obtenerSaldoAnterior($value['Naturaleza'], $nits, $j, $tipo_reporte, $nit['Nit'], $value['Id_Plan_Cuentas']);
                    $debito = $nit['Total_Debito_' . $tipo_reporte];
                    $credito = $nit['Total_Credito_' . $tipo_reporte];
                    $nuevo_saldo = $this->calcularNuevoSaldo($value['Naturaleza'], $saldo_anterior, $debito, $credito);

                    if ($saldo_anterior != 0 || $debito != 0 || $credito != 0 || $nuevo_saldo != 0) {
                        $contenido .= '<tr>
                            <td style="font-size:9px;color:gray;padding:2px;text-align:left;border:1px solid #cccccc;">
                                ' . $nit['Nit'] . '
                            </td>
                            <td style="width:250px;font-size:9px;color:gray;padding:2px;text-align:left;border:1px solid #cccccc;">
                                ' . $nit['Nombre'] . '
                            </td>
                            <td style="font-size:9px;color:gray;padding:2px;text-align:right;border:1px solid #cccccc;">
                                $ ' . number_format($saldo_anterior, 2, ",", ".") . '
                            </td>
                            <td style="font-size:9px;color:gray;padding:2px;text-align:right;border:1px solid #cccccc;">
                                $ ' . number_format($debito, 2, ",", ".") . '
                            </td>
                            <td style="font-size:9px;color:gray;padding:2px;text-align:right;border:1px solid #cccccc;">
                                $ ' . number_format($credito, 2, ",", ".") . '
                            </td>
                            <td style="font-size:9px;color:gray;padding:2px;text-align:right;border:1px solid #cccccc;">
                                $ ' . number_format($nuevo_saldo, 2, ",", ".") . '
                            </td>
                        </tr>';
                    }
                }
            }

            $totales['saldo_anterior'] = $this->getTotal($totales, 'saldo_anterior');

            $totales['nuevo_saldo'] = $this->getTotal($totales, 'nuevo_saldo');

            $contenido .= '
                <tr>

                        <td colspan="2" style="background:#cecece;padding:4px;text-align:left;border:1px solid #cccccc;">
                            Total
                        </td>
                        <td style="background:#cecece;padding:4px;text-align:right;border:1px solid #cccccc;">
                            $ ' . number_format($totales['saldo_anterior'], 2, ",", ".") . '
                        </td>
                        <td style="background:#cecece;padding:4px;text-align:right;border:1px solid #cccccc;">
                        $ ' . number_format($totales['debito'], 2, ",", ".") . '
                        </td>
                        <td style="background:#cecece;padding:4px;text-align:right;border:1px solid #cccccc;">
                        $ ' . number_format($totales['credito'], 2, ",", ".") . '
                        </td>
                        <td style="background:#cecece;padding:4px;text-align:right;border:1px solid #cccccc;">
                        $ ' . number_format($totales['nuevo_saldo'], 2, ",", ".") . '
                        </td>
                    </tr>';
        } elseif ($tipo == 'Tipo') {

            $query = "SELECT

                PC.Id_Plan_Cuentas,
                PC.Codigo,
                PC.Nombre,
                Codigo_Niif,
                Nombre_Niif,
                PC.Naturaleza,
                IFNULL(SUM(BIC.Debito_PCGA), (SELECT IFNULL(SUM(Debito_PCGA),0) FROM Balance_Inicial_Contabilidad WHERE Fecha = '$ultimo_dia_mes' AND Codigo_Cuenta LIKE CONCAT(PC.$column_1,'%'))) AS Debito_PCGA,
                IFNULL(SUM(BIC.Credito_PCGA), (SELECT IFNULL(SUM(Credito_PCGA),0) FROM Balance_Inicial_Contabilidad WHERE Fecha = '$ultimo_dia_mes' AND Codigo_Cuenta LIKE CONCAT(PC.$column_1,'%'))) AS Credito_PCGA,
                IFNULL(SUM(BIC.Debito_NIIF), (SELECT IFNULL(SUM(Debito_NIIF),0) FROM Balance_Inicial_Contabilidad WHERE Fecha = '$ultimo_dia_mes' AND Codigo_Cuenta LIKE CONCAT(PC.$column_2,'%'))) AS Debito_NIIF,
                IFNULL(SUM(BIC.Credito_NIIF), (SELECT IFNULL(SUM(Credito_NIIF),0) FROM Balance_Inicial_Contabilidad WHERE Fecha = '$ultimo_dia_mes' AND Codigo_Cuenta LIKE CONCAT(PC.$column_2,'%'))) AS Credito_NIIF,
                PC.Estado,
                PC.Movimiento,
                PC.Tipo_P
            FROM
                Plan_Cuentas PC
                    LEFT JOIN
                 (SELECT * FROM Balance_Inicial_Contabilidad WHERE Fecha = '$ultimo_dia_mes') BIC ON BIC.Id_Plan_Cuentas = PC.Id_Plan_Cuentas
                 " . $this->getStrCondiciones() . "
                 GROUP BY PC.Id_Plan_Cuentas
            HAVING Estado = 'ACTIVO' OR (Estado = 'INACTIVO' AND (Debito_PCGA > 0 OR Credito_PCGA > 0 OR Debito_NIIF > 0 OR Credito_NIIF > 0))
            ORDER BY PC.$column";

            $oCon = new consulta();
            $oCon->setQuery($query);
            $oCon->setTipo('Multiple');
            $balance = $oCon->getData();
            unset($oCon);
            /* echo "<pre>";
                var_dump($balance);
                echo "</pre>";
                exit; */

            foreach ($balance as $i => $value) {
                $balance[$i]['tipos'] = $this->getMovimientosPorTipo($fecha_ini, $fecha_fin, $value['Id_Plan_Cuentas'], $value['Movimiento']);
            }

            $acum_saldo_anterior = 0;
            $acum_debito = 0;
            $acum_credito = 0;
            $acum_nuevo_saldo = 0;

            foreach ($balance as $i => $value) {

                $codigo = $tipo_reporte == 'Pcga' ? $value['Codigo'] : $value['Codigo_Niif'];
                $nombre_cta = $tipo_reporte == 'Pcga' ? $value['Nombre'] : $value['Nombre_Niif'];

                $saldo_anterior = $this->obtenerSaldoAnterior($value['Naturaleza'], $balance, $i, $tipo_reporte);
                $debito = $this->calcularDebito($codigo, $value['Tipo_P'], $movimientos);
                $credito = $this->calcularCredito($codigo, $value['Tipo_P'], $movimientos);
                $nuevo_saldo = $this->calcularNuevoSaldo($value['Naturaleza'], $saldo_anterior, $debito, $credito);

                if ($saldo_anterior != 0 || $debito != 0 || $credito != 0 || $nuevo_saldo != 0) {
                    $contenido .= '
                        <tr>
                            <td style="padding:4px;text-align:left;border:1px solid #cccccc;">
                                ' . $codigo . '
                            </td>
                            <td style="width:250px;padding:4px;text-align:left;border:1px solid #cccccc;">
                                ' . $nombre_cta . '
                            </td>
                            <td style="padding:4px;text-align:right;border:1px solid #cccccc;">
                                $ ' . number_format($saldo_anterior, 2, ",", ".") . '
                            </td>
                            <td style="padding:4px;text-align:right;border:1px solid #cccccc;">
                            $ ' . number_format($debito, 2, ",", ".") . '
                            </td>
                            <td style="padding:4px;text-align:right;border:1px solid #cccccc;">
                            $ ' . number_format($credito, 2, ",", ".") . '
                            </td>
                            <td style="padding:4px;text-align:right;border:1px solid #cccccc;">
                            $ ' . number_format($nuevo_saldo, 2, ",", ".") . '
                            </td>

                        </tr>';

                    /* $acum_saldo_anterior += $saldo_anterior;
                        $acum_debito += $debito;
                        $acum_credito += $credito;
                        $acum_nuevo_saldo += $nuevo_saldo; */

                    if ($value['Tipo_P'] == 'CLASE') {
                        $totales['clases'][$value['Codigo']]['saldo_anterior'] = $saldo_anterior;
                        $totales['clases'][$value['Codigo']]['nuevo_saldo'] = $nuevo_saldo;
                        //La formula para estos campos: 1+2+3+4+5+6+8 (A nivel de cuentas)
                        $totales['debito'] += $debito;
                        $totales['credito'] += $credito;
                    }
                }

                $tipos = $value['tipos'];

                if (count($tipos) > 0) {
                    $debe = $tipo_reporte == 'Pcga' ? 'Debe' : 'Debe_Niif';
                    $haber = $tipo_reporte == 'Pcga' ? 'Haber' : 'Haber_Niif';
                    $contenido .= '
                            <tr>
                                <td style="padding:4px;text-align:left;border:1px solid #cccccc;">
                                </td>
                                <td colspan="5" style="padding:4px;text-align:left;border:1px solid #cccccc;">
                                    <table>';
                    foreach ($tipos as $z => $value) {
                        $contenido .= '
                                            <tr>
                                                <td style="width:50px;font-size:9px;color:gray;padding:2px;text-align:left;">
                                                ' . $value['Prefijo'] . '
                                                </td>
                                                <td style="width:160px;font-size:9px;color:gray;padding:2px;text-align:left;">
                                                ' . $value['Tipo_Documento'] . '
                                                </td>
                                                <td style="width:100px;font-size:9px;color:gray;padding:2px;text-align:right;">
                                                0,00
                                                </td>
                                                <td style="width:100px;font-size:9px;color:gray;padding:2px;text-align:right;">
                                                $ ' . number_format($value[$debe], 2, ",", ".") . '
                                                </td>
                                                <td style="width:100px;font-size:9px;color:gray;padding:2px;text-align:right;">
                                                $ ' . number_format($value[$haber], 2, ",", ".") . '
                                                </td>
                                                <td style="width:90px;font-size:9px;color:gray;padding:2px;text-align:right;">
                                                0,00
                                                </td>
                                            </tr>';
                    }
                    $contenido .= '</table>
                                </td>
                            </tr>
                        ';
                }
            }

            $totales['saldo_anterior'] = $this->getTotal($totales, 'saldo_anterior');

            $totales['nuevo_saldo'] = $this->getTotal($totales, 'nuevo_saldo');

            $contenido .= '
                <tr>

                        <td colspan="2" style="background:#cecece;padding:4px;text-align:left;border:1px solid #cccccc;">
                            Total
                        </td>
                        <td style="background:#cecece;padding:4px;text-align:right;border:1px solid #cccccc;">
                            $ ' . number_format($totales['saldo_anterior'], 2, ",", ".") . '
                        </td>
                        <td style="background:#cecece;padding:4px;text-align:right;border:1px solid #cccccc;">
                        $ ' . number_format($totales['debito'], 2, ",", ".") . '
                        </td>
                        <td style="background:#cecece;padding:4px;text-align:right;border:1px solid #cccccc;">
                        $ ' . number_format($totales['credito'], 2, ",", ".") . '
                        </td>
                        <td style="background:#cecece;padding:4px;text-align:right;border:1px solid #cccccc;">
                        $ ' . number_format($totales['nuevo_saldo'], 2, ",", ".") . '
                        </td>
                    </tr>';
        }

        $contenido .= '</table>';




        /* CABECERA GENERAL DE TODOS LOS ARCHIVOS PDF*/
        $cabecera = '<table style="" >
                      <tbody>
                        <tr>
                          <td style="width:70px;">
                            <img src="' . $_SERVER["DOCUMENT_ROOT"] . 'assets/images/LogoProh.jpg" style="width:60px;" alt="Pro-H Software" />
                          </td>
                          <td class="td-header" style="width:410px;font-weight:thin;font-size:14px;line-height:20px;">
                            ' . $config["Nombre_Empresa"] . '<br>
                            N.I.T.: ' . $config["NIT"] . '<br>
                            ' . $config["Direccion"] . '<br>
                            TEL: ' . $config["Telefono"] . '
                          </td>
                          <td style="width:250px;text-align:right">
                                ' . $codigos . '
                          </td>
                        </tr>
                      </tbody>
                    </table><hr style="border:1px dotted #ccc;width:730px;">';
        /* FIN CABECERA GENERAL DE TODOS LOS ARCHIVOS PDF*/

        /* CONTENIDO GENERAL DEL ARCHIVO MEZCLANDO TODA LA INFORMACION*/
        $content = '<page backtop="0mm" backbottom="0mm">
                        <div class="page-content" >' .
            $cabecera .
            $contenido . '
                        </div>
                    </page>';
        /* FIN CONTENIDO GENERAL DEL ARCHIVO MEZCLANDO TODA LA INFORMACION*/

        $pdf = Pdf::loadHTML($content);
        return $pdf->download('NIIF.pdf');
        /* try {
            $html2pdf = new HTML2PDF('P', 'A4', 'Es', true, 'UTF-8', array(5, 5, 5, 5));
            $html2pdf->writeHTML($content);
            $direc = 'Balance Prueba.pdf'; // NOMBRE DEL ARCHIVO ES EL CODIGO DEL DOCUMENTO
            $html2pdf->Output($direc); // LA D SIGNIFICA DESCARGAR, 'F' SE PODRIA HACER PARA DEJAR EL ARCHIVO EN UNA CARPETA ESPECIFICA
        } catch (HTML2PDF_exception $e) {
            echo $e;
            exit;
        } */
    }

    function getStrCondiciones()
    {
        global $tipo_reporte;
        global $nivel_reporte;
        global $cta_ini;
        global $cta_fin;

        $condicion = '';

        $column = $tipo_reporte == 'Pcga' ? 'Codigo' : 'Codigo_Niif';
        if (isset($cta_ini) && $cta_ini != '') {
            $condicion .= " WHERE $column BETWEEN '$cta_ini' AND '$cta_fin'";
        }
        if (isset($nivel_reporte) && $nivel_reporte != '') {
            if ($condicion == '') {
                $condicion .= " WHERE CHAR_LENGTH($column) BETWEEN 1 AND $nivel_reporte";
            } else {
                $condicion .= " AND CHAR_LENGTH($column) BETWEEN 1 AND $nivel_reporte";
            }
        }

        return $condicion;
    }

    function obtenerSaldoAnterior($naturaleza, $array, $index, $tipo_reporte, $nit = null, $plan = null)
    {
        global $fecha_ini;
        global $movimientos;

        $value = $tipo_reporte == 'Pcga' ? 'Codigo' : 'Codigo_Niif';

        $saldo_anterior = 0;
        $tipo_reporte = strtoupper($tipo_reporte);
        if ($naturaleza == 'D') { // Si es naturaleza debito, suma, de lo contrario, resta
            $saldo_anterior = $array[$index]["Debito_$tipo_reporte"] - $array[$index]["Credito_$tipo_reporte"];
        } else {
            $saldo_anterior = $array[$index]["Credito_$tipo_reporte"] - $array[$index]["Debito_$tipo_reporte"];
        }

        $fecha1 = date('Y-m-d', strtotime($fecha_ini));

        # VALIDACIÓN POR SI LA FECHA DE INICIO NO ES EL DÍA UNO (1) DEL MES Y SE TOQUE SACAR EL SALDO DE LA DIFERENCIA DEL ULTIMO BALANCE INICIAL.

        if ($fecha1 != '2019-01-01') {

            if ($nit === null) {
                // $fecha1 = date('Y-01-01', strtotime($fecha_ini)); // Primer día del mes de enero
                $fecha1 = '2019-01-01';
                $fecha2 = strtotime('-1 day', strtotime($fecha_ini)); // Un día antes de la fecha de inicio para sacar el corte de saldo final.
                $fecha2 = date('Y-m-d', $fecha2);
                $movimientos_lista = $this->getMovimientosCuenta($fecha1, $fecha2);
                $codigo = $array[$index][$value];
                $tipo = $array[$index]['Tipo_P'];
                $debito = $this->calcularDebito($codigo, $tipo, $movimientos_lista);
                $credito = $this->calcularCredito($codigo, $tipo, $movimientos_lista);
                $saldo_anterior = $this->calcularNuevoSaldo($naturaleza, $saldo_anterior, $debito, $credito);
            } else {
                // $fecha1 = date('Y-01-01', strtotime($fecha_ini)); // Primer día del mes de enero
                $fecha1 = '2019-01-01';
                $fecha2 = strtotime('-1 day', strtotime($fecha_ini)); // Un día antes de la fecha de inicio para sacar el corte de saldo final.
                $fecha2 = date('Y-m-d', $fecha2);
                $movimientos_lista = $this->getMovimientosCuenta($fecha1, $fecha2, $nit, $plan);
                $debito = $movimientos_lista['Debito'];
                $credito = $movimientos_lista['Credito'];
                $saldo_anterior = $this->calcularNuevoSaldo($naturaleza, $saldo_anterior, $debito, $credito);
            }
        }

        return $saldo_anterior;
    }

    function compararCuenta($codigo, $nivel, $cod_cuenta_actual)
    {
        /* var_dump(func_get_args());
            echo "<br>"; */
        $str_comparar = substr($cod_cuenta_actual, 0, $nivel);

        if (strpos($str_comparar, $codigo) !== false) {
            return true;
        }

        return false;
    }

    function calcularDebito($codigo, $tipo_cuenta, $movimientos)
    {
        $codigos_temp = [];
        global $tipo_reporte;

        foreach ($movimientos as $mov) {
            $nivel = strlen($mov['Codigo']);
            $nivel2 = strlen($codigo);
            $cod_superior = '';
            $restar_str = 0;
            $cod_mov = $tipo_reporte == 'Pcga' ? $mov['Codigo'] : $mov['Codigo_Niif'];

            if ($this->compararCuenta($codigo, $nivel2, $cod_mov)) {
                $codigos_temp[$cod_mov] = $mov['Debito'];
                while ($nivel > $nivel2) {
                    if ($nivel > 2) {
                        $restar_str += 2;

                        $str = $cod_mov;
                        $count_str = strlen($str);
                        $cod_superior = substr($str, 0, $count_str - $restar_str);

                        if (!array_key_exists($cod_superior, $codigos_temp)) {
                            $codigos_temp[$cod_superior] = $mov['Debito'];
                        } else {
                            $codigos_temp[$cod_superior] += $mov['Debito'];
                        }
                        $nivel -= 2;
                    } else {
                        $restar_str += 1;

                        $str = $cod_mov;
                        $count_str = strlen($str);
                        $cod_superior = substr($str, 0, $count_str - $restar_str);
                        if (!array_key_exists($cod_superior, $codigos_temp)) {
                            $codigos_temp[$cod_superior] = $mov['Debito'];
                        } else {
                            $codigos_temp[$cod_superior] += $mov['Debito'];
                        }
                        $nivel -= 1;
                    }
                }
            }
        }

        return isset($codigos_temp[$codigo]) ? $codigos_temp[$codigo] : '0';
    }

    function calcularCredito($codigo, $tipo_cuenta, $movimientos)
    {
        // return '0'; // Esto es temporal.
        global $tipo_reporte;

        $codigos_temp = [];

        foreach ($movimientos as $mov) {
            $nivel = strlen($mov['Codigo']);
            $nivel2 = strlen($codigo);
            $cod_superior = '';
            $restar_str = 0;
            $cod_mov = $tipo_reporte == 'Pcga' ? $mov['Codigo'] : $mov['Codigo_Niif'];

            /* echo "++". $mov['Codigo'] ."<br>";
                echo "--". $codigo ."<br>";

                var_dump(compararCuenta($codigo, $nivel2, $cod_mov));
                echo "<br>"; */

            if ($this->compararCuenta($codigo, $nivel2, $cod_mov)) {
                $codigos_temp[$cod_mov] = $mov['Credito'];
                while ($nivel > $nivel2) {
                    if ($nivel > 2) {
                        $restar_str += 2;

                        // echo "cod superior A.N -- " . $cod_superior . "<br>";
                        // echo "Nivel -- " . $nivel . " -- Resta -- " . $restar_str . "<br>";
                        $str = $cod_mov;
                        $count_str = strlen($str);
                        $cod_superior = substr($str, 0, $count_str - $restar_str);
                        // echo "cod superior -- " . $cod_superior . "<br>";


                        if (!array_key_exists($cod_superior, $codigos_temp)) {
                            $codigos_temp[$cod_superior] = $mov['Credito'];
                        } else {
                            $codigos_temp[$cod_superior] += $mov['Credito'];
                        }
                        $nivel -= 2;
                    } else {
                        $restar_str += 1;
                        // echo "cod superior A.N -- " . $cod_superior . "<br>";
                        // echo "Nivel -- " . $nivel . " -- Resta -- " . $restar_str . "<br>";
                        $str = $cod_mov;
                        $count_str = strlen($str);
                        $cod_superior = substr($str, 0, $count_str - $restar_str);
                        // echo "cod superior -- " . $cod_superior . "<br><br>";
                        if (!array_key_exists($cod_superior, $codigos_temp)) {
                            $codigos_temp[$cod_superior] = $mov['Credito'];
                        } else {
                            $codigos_temp[$cod_superior] += $mov['Credito'];
                        }
                        $nivel -= 1;
                    }
                }
            }
        }

        /* echo "<pre>";
            var_dump($codigos_temp);
            echo "</pre>"; */
        // exit;

        return isset($codigos_temp[$codigo]) ? $codigos_temp[$codigo] : '0';
    }

    function calcularNuevoSaldo($naturaleza, $saldo_anterior, $debito, $credito)
    {
        $nuevo_saldo = 0;

        if ($naturaleza == 'D') { // Si es naturaleza debito, suma, de lo contrario, resta
            $nuevo_saldo = ((float) $saldo_anterior + (float) $debito) - (float) $credito;
        } else {
            $nuevo_saldo = ((float) $saldo_anterior + (float) $credito) - (float) $debito;
        }

        return $nuevo_saldo;
    }

    function nitsPorCuentasContables($id_plan_cuentas)
    {
        global $fecha_ini;
        global $fecha_fin;
        global $nivel_reporte;

        $query = "SELECT
        r.Nit,
        r.Nombre,
        SUM(r.Debito_PCGA) AS Debito_PCGA,
        SUM(r.Credito_PCGA) AS Credito_PCGA,
        SUM(r.Debito_NIIF) AS Debito_NIIF,
        SUM(r.Credito_NIIF) AS Credito_NIIF,
        SUM(r.Total_Debito_Pcga) AS Total_Debito_Pcga,
        SUM(r.Total_Credito_Pcga) AS Total_Credito_Pcga,
        SUM(r.Total_Debito_Niif) AS Total_Debito_Niif,
        SUM(r.Total_Credito_Niif) AS Total_Credito_Niif
        FROM
        (
        (SELECT

            BIC.Nit,
            (
                CASE BIC.Tipo
                    WHEN 'Cliente' THEN (SELECT IF((first_name IS NULL OR first_name = ''), social_reason, CONCAT_WS(' ', first_name, first_surname)) AS Nombre FROM third_parties WHERE id = BIC.Nit AND third_party_type = 'Cliente')
                    WHEN 'Proveedor' THEN (SELECT IF((first_name IS NULL OR first_name = ''), social_reason, CONCAT_WS(' ', first_name, first_surname)) AS Nombre FROM third_parties WHERE id = BIC.Nit AND third_party_type = 'Proveedor')
                    WHEN 'Funcionario' THEN (SELECT CONCAT_WS(' ',first_name,first_surname) FROM people WHERE id = BIC.Nit)
                END
            ) AS Nombre,
            BIC.Debito_PCGA,
            BIC.Credito_PCGA,
            BIC.Debito_NIIF,
            BIC.Credito_NIIF,
            0 AS Total_Debito_Pcga,
            0 AS Total_Credito_Pcga,
            0 AS Total_Debito_Niif,
            0 AS Total_Credito_Niif
        FROM
        Balance_Inicial_Contabilidad BIC
        WHERE
            BIC.Id_Plan_Cuentas = $id_plan_cuentas AND BIC.Nit != 0
        ORDER BY BIC.Nit)
        UNION ALL
        (
        SELECT

            M.Nit,
            (
                CASE M.Tipo_Nit
                    WHEN 'Cliente' THEN (SELECT IF((first_name IS NULL OR first_name = ''), social_reason, CONCAT_WS(' ', first_name, first_surname)) AS Nombre FROM third_parties WHERE id = M.Nit AND third_party_type = 'Cliente')
                    WHEN 'Proveedor' THEN (SELECT IF((first_name IS NULL OR first_name = ''), social_reason, CONCAT_WS(' ', first_name, first_surname)) AS Nombre FROM third_parties WHERE id = M.Nit AND third_party_type = 'Proveedor')
                    WHEN 'Funcionario' THEN (SELECT CONCAT_WS(' ',first_name,first_surname) FROM people WHERE id = M.Nit)
                END
            ) AS Nombre,
            0 AS Debito_PCGA,
            0 AS Credito_PCGA,
            0 AS Debito_NIIF,
            0 AS Credito_NIIF,
            0 AS Total_Debito_Pcga,
            0 AS Total_Credito_Pcga,
            0 AS Total_Debito_Niif,
            0 AS Total_Credito_Niif
        FROM
        Movimiento_Contable M
        WHERE
            M.Id_Plan_Cuenta = $id_plan_cuentas AND M.Nit != 0  AND M.Estado != 'Anulado'
        GROUP BY M.Nit, M.Id_Plan_Cuenta
        ORDER BY M.Nit
        )

        UNION ALL
        (
        SELECT
            M.Nit,
            (
                CASE M.Tipo_Nit
                    WHEN 'Cliente' THEN (SELECT IF((first_name IS NULL OR first_name = ''), social_reason, CONCAT_WS(' ', first_name, first_surname)) AS Nombre FROM third_parties WHERE id = M.Nit AND third_party_type = 'Cliente')
                    WHEN 'Proveedor' THEN (SELECT IF((first_name IS NULL OR first_name = ''), social_reason, CONCAT_WS(' ', first_name, first_surname)) AS Nombre FROM third_parties WHERE id = M.Nit AND third_party_type = 'Proveedor')
                    WHEN 'Funcionario' THEN (SELECT CONCAT_WS(' ',first_name,first_surname) FROM people WHERE id = M.Nit)
                END
            ) AS Nombre,
            0 AS Debito_PCGA,
            0 AS Credito_PCGA,
            0 AS Debito_NIIF,
            0 AS Credito_NIIF,
            SUM(M.Debe) AS Total_Debito_Pcga,
            SUM(M.Haber) AS Total_Credito_Pcga,
            SUM(M.Debe_Niif) AS Total_Debito_Niif,
            SUM(M.Haber_Niif) AS Total_Credito_Niif
        FROM
        Movimiento_Contable M
        WHERE
            M.Id_Plan_Cuenta = $id_plan_cuentas AND M.Nit != 0 AND DATE(M.Fecha_Movimiento) BETWEEN '$fecha_ini' AND '$fecha_fin' AND M.Estado != 'Anulado'
        GROUP BY M.Nit, M.Id_Plan_Cuenta
        ORDER BY M.Nit
        )
        ) r
        GROUP BY r.Nit";
        //echo $query; exit;

        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->setTipo('Multiple');
        $resultado = $oCon->getData();
        unset($oCon);

        return $resultado;
    }

    function getMovimientosCuenta($fecha1, $fecha2, $nit = null, $plan = null)
    {
        global $tipo_reporte;

        $tipo = $tipo_reporte != 'Pcga' ? '_Niif' : '';

        if ($nit === null) {
            $query = "SELECT MC.Id_Plan_Cuenta, PC.Codigo, PC.Nombre, PC.Codigo_Niif, PC.Nombre_Niif, SUM(Debe$tipo) AS Debito, SUM(Haber$tipo) AS Credito FROM Movimiento_Contable MC INNER JOIN Plan_Cuentas PC ON MC.Id_Plan_Cuenta = PC.Id_Plan_Cuentas WHERE DATE(Fecha_Movimiento) BETWEEN '$fecha1' AND '$fecha2' AND MC.Estado != 'Anulado' GROUP BY MC.Id_Plan_Cuenta";

            $oCon = new consulta();
            $oCon->setQuery($query);
            $oCon->setTipo('Multiple');
            $movimientos = $oCon->getData();
            unset($oCon);
        } else {
            $query = "SELECT MC.Id_Plan_Cuenta, SUM(Debe$tipo) AS Debito, SUM(Haber$tipo) AS Credito FROM Movimiento_Contable MC WHERE DATE(Fecha_Movimiento) BETWEEN '$fecha1' AND '$fecha2' AND MC.Nit = $nit AND MC.Id_Plan_Cuenta = $plan AND MC.Estado != 'Anulado'";

            $oCon = new consulta();
            $oCon->setQuery($query);
            $movimientos = $oCon->getData();
            unset($oCon);
        }


        return $movimientos;
    }

    function getUltimoDiaMes($fecha_inicio)
    {
        // $ultimo_dia_mes = date("Y-m-d",(mktime(0,0,0,date("m",strtotime($fecha_inicio)),1,date("Y",strtotime($fecha_inicio)))-1));
        $ultimo_dia_mes = '2018-12-31'; // Modificado 16-07-2019 -- KENDRY

        return $ultimo_dia_mes;
    }

    function getMovimientosPorTipo($fecha_ini, $fecha_fin, $id_plan_cuenta, $movimiento)
    {
        if ($movimiento == 'S') {
            $query = "SELECT
                MC.Id_Modulo,
                M.Documento AS Tipo_Documento,
                M.Prefijo,
                SUM(Debe) AS Debe,
                SUM(Haber) AS Haber,
                SUM(Debe_Niif) AS Debe_Niif,
                SUM(Haber_Niif) AS Haber_Niif
                FROM Movimiento_Contable MC
                INNER JOIN Modulo M ON MC.Id_Modulo = M.Id_Modulo
                WHERE MC.Estado != 'Anulado'
                AND DATE(MC.Fecha_Movimiento) BETWEEN '$fecha_ini' AND '$fecha_fin'
                AND MC.Id_Plan_Cuenta = $id_plan_cuenta
                GROUP BY MC.Id_Modulo";

            $oCon = new consulta();
            $oCon->setQuery($query);
            $oCon->setTipo('Multiple');
            $resultado = $oCon->getData();
            unset($oCon);

            return $resultado;
        }
    }

    function armarTotales($totales)
    {
        $cuentas_clases = [
            "1" => [
                "saldo_anterior" => isset($totales['clases']['1']) ? $totales['clases']['1']['saldo_anterior'] : 0,
                "nuevo_saldo" => isset($totales['clases']['1']) ? $totales['clases']['1']['nuevo_saldo'] : 0
            ],
            "2" => [
                "saldo_anterior" => isset($totales['clases']['2']) ? $totales['clases']['2']['saldo_anterior'] : 0,
                "nuevo_saldo" => isset($totales['clases']['2']) ? $totales['clases']['2']['nuevo_saldo'] : 0
            ],
            "3" => [
                "saldo_anterior" => isset($totales['clases']['3']) ? $totales['clases']['3']['saldo_anterior'] : 0,
                "nuevo_saldo" => isset($totales['clases']['3']) ? $totales['clases']['3']['nuevo_saldo'] : 0
            ],
            "4" => [
                "saldo_anterior" => isset($totales['clases']['4']) ? $totales['clases']['4']['saldo_anterior'] : 0,
                "nuevo_saldo" => isset($totales['clases']['4']) ? $totales['clases']['4']['nuevo_saldo'] : 0
            ],
            "5" => [
                "saldo_anterior" => isset($totales['clases']['5']) ? $totales['clases']['5']['saldo_anterior'] : 0,
                "nuevo_saldo" => isset($totales['clases']['5']) ? $totales['clases']['5']['nuevo_saldo'] : 0
            ],
            "6" => [
                "saldo_anterior" => isset($totales['clases']['6']) ? $totales['clases']['6']['saldo_anterior'] : 0,
                "nuevo_saldo" => isset($totales['clases']['6']) ? $totales['clases']['6']['nuevo_saldo'] : 0
            ]
        ];

        return $cuentas_clases;
    }


    function getTotal($totales, $tipo)
    {
        $cuentas_clases = $this->armarTotales($totales);
        $total = 0;

        if ($tipo == 'saldo_anterior') {
            $total = ($cuentas_clases["1"]["saldo_anterior"] - $cuentas_clases["2"]["saldo_anterior"] - $cuentas_clases["3"]["saldo_anterior"]) - ($cuentas_clases["4"]["saldo_anterior"] - $cuentas_clases["5"]["saldo_anterior"] - $cuentas_clases["6"]["saldo_anterior"]);
        } elseif ($tipo == 'nuevo_saldo') {
            $total = ($cuentas_clases["1"]["nuevo_saldo"] - $cuentas_clases["2"]["nuevo_saldo"] - $cuentas_clases["3"]["nuevo_saldo"]) - ($cuentas_clases["4"]["nuevo_saldo"] - $cuentas_clases["5"]["nuevo_saldo"] - $cuentas_clases["6"]["nuevo_saldo"]);
        }

        return $total;
    }

    public function getPlanCuentas()
    {
        $filtros = isset($_REQUEST['filtros']) ? $_REQUEST['filtros'] : false;
        $filtros = json_decode($filtros, true);

        $tipoCierre = isset($_REQUEST['tipoCierre']) ? $_REQUEST['tipoCierre'] : false;

        $cond = '';
        if ($filtros) {
            if ($filtros['codigo'] != '') {
                # code...
                $cond .= ' AND Codigo LIKE "' . $filtros['codigo'] . '%" ';
            }
            if ($filtros['nombre'] != '') {
                # code...
                $cond .= ' AND Nombre LIKE "' . $filtros['nombre'] . '%" ';
            }
            if ($filtros['tipoCierre'] != '') {
                # code...
                $cond .= ' AND Tipo_Cierre_' . $tipoCierre . ' LIKE "' . $filtros['tipoCierre'] . '%" ';
            }
        }

        $query = 'SELECT Id_Plan_Cuentas, Codigo, Nombre, Tipo_Cierre_' . $tipoCierre . '
		    FROM Plan_Cuentas
		    WHERE Estado = "ACTIVO" ' . $cond . '
		    ORDER BY Codigo';

        //Se crea la instancia que contiene la consulta a realizar
        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->setTipo('Multiple');
        $planes = $oCon->getData();
        unset($oCon);

        $res['type'] = $planes ? 'success' : 'error';
        $res['query_result'] = $planes;

        //Ejecuta la consulta de la instancia queryobj y retorna el resultado de la misma segun los parametros

        return response()->json($res);
    }

    public function validateExcel(Request $request, $delete)
    {
        //dd($delete);
        $file = base64_decode(
            preg_replace(
                "#^data:application/\w+;base64,#i",
                "",
                $request->file
            )
        );
        if (AccountPlanBalance::count() && AccountPlanBalance::where('balance', '<>', 0)->count()) {
            return $this->success(
                response()->json([
                    'icon' => 'error',
                    'title' => 'Error',
                    'text' => 'No podemos hacer esto porque ya hay saldos iniciales.',
                    'timer' => 0
                ])
            );
        } else {
            if ($delete == 'true') {
                PlanCuentas::truncate();
                AccountPlanBalance::truncate();
            }
            $file_path = '/imports/' . Str::random(30) . time() . '.xlsx';
            Storage::disk('public')->put($file_path, $file, "public");
            Excel::import(new AccountPlansImport, $file_path, 'public');
            return $this->success(
                response()->json([
                    'icon' => 'success',
                    'title' => 'Operación exitosa',
                    'text' => 'PUC comercial cargado correctamente.',
                    'timer' => 1000
                ])
            );
        }
    }


    public function importInitialBalances(Request $request)
    {
        $file = base64_decode(
            preg_replace(
                "#^data:application/\w+;base64,#i",
                "",
                $request->file
            )
        );
        $file_path = '/imports/' . Str::random(30) . time() . '.xlsx';
        Storage::disk('public')->put($file_path, $file, "public");
        Excel::import(new InitialBalanceImport, $file_path, 'public');
        return $this->success('Operacion existosa');
    }

    public function paginate2()
    {
        return $this->success(
            PlanCuentas::with('cuenta_padre')
                ->whereRaw('LENGTH(Codigo_Niif) = 1')
                ->get(['Id_Plan_Cuentas', 'Nombre_Niif', 'Codigo_Niif', 'Codigo_Padre'])
        );
    }

    public function paginate(Request $request)
    {
        $pagina = (isset($_REQUEST['pag']) ? $_REQUEST['pag'] : '');
        $condicion = $this->SetCondiciones($_REQUEST);
        $query_paginacion = 'SELECT COUNT(*) AS Total
                        FROM Plan_Cuentas as pc'
            . $condicion;
        $query = 'SELECT pc.*, CONCAT(pc.Codigo, " - ", pc.Nombre) as code, c.name as Empresa, pc2.Nombre as Cuenta_Padre_Nombre
                FROM Plan_Cuentas as pc
                LEFT JOIN companies as c ON c.id = pc.company_id
                LEFT JOIN Plan_Cuentas as pc2 ON pc.Codigo_Padre = pc2.Codigo_Niif'
            . $condicion . ' ORDER BY Codigo';
        $paginationObj = new PaginacionData(20, $query_paginacion, $pagina);
        $queryObj = new QueryBaseDatos($query);
        $result = $queryObj->Consultar('Multiple', true, $paginationObj);

        return response()->json($result);
    }


    public function SetCondiciones($req)
    {
        $condicion = '';

        if (isset($req['nombre']) && $req['nombre'] != "") {
            $condicion .= " WHERE pc.Nombre LIKE '%" . $req['nombre'] . "%'";
        }

        if (isset($req['nombre_niif']) && $req['nombre_niif']) {
            if ($condicion != "") {
                $condicion .= " AND pc.Nombre_Niif LIKE '%" . $req['nombre_niif'] . "%'";
            } else {
                $condicion .= " WHERE pc.Nombre_Niif LIKE '%" . $req['nombre_niif'] . "%'";
            }
        }

        if (isset($req['cod']) && $req['cod']) {
            if ($condicion != "") {
                $condicion .= " AND pc.Codigo LIKE '" . $req['cod'] . "%'";
            } else {
                $condicion .= " WHERE pc.Codigo LIKE '" . $req['cod'] . "%'";
            }
        }

        if (isset($req['cod_niif']) && $req['cod_niif']) {
            if ($condicion != "") {
                $condicion .= " AND pc.Codigo_Niif LIKE '" . $req['cod_niif'] . "%'";
            } else {
                $condicion .= " WHERE pc.Codigo_Niif LIKE '" . $req['cod_niif'] . "%'";
            }
        }

        if (isset($req['estado']) && $req['estado']) {
            if ($condicion != "") {
                $condicion .= " AND pc.Estado = '" . $req['estado'] . "'";
            } else {
                $condicion .= " WHERE pc.Estado = '" . $req['estado'] . "'";
            }
        }

        if (isset($req['company_id']) && $req['company_id']) {
            if ($condicion != "") {
                $condicion .= " AND pc.company_id = '" . $req['company_id'] . "'";
            } else {
                $condicion .= " WHERE pc.company_id = '" . $req['company_id'] . "'";
            }
        }

        return $condicion;
    }

    public function descargarExcel(Request $request)
    {
        $plan = PlanCuentas::where('company_id', $request->id)->orderBy('Codigo')->get();
        return Excel::download(new PlanCuentasExport($plan), 'PUC.xlsx');
    }

    public function cambiarEstado()
    {
        $respuesta = array();

        $id_cuenta = (isset($_REQUEST['id_cuenta']) ? $_REQUEST['id_cuenta'] : '');
        //$id_cuenta = json_decode($id_cuenta);

        if ($id_cuenta == '') {
            $respuesta["msg"] = "No se envio un plan de cuenta para proceder, contacte con el administrador!";
            $respuesta["icon"] = "error";
            $respuesta["title"] = "Error al guardar!";
            return response()->json($respuesta);
        }

        /* $oItem = new complex("Plan_Cuentas", "Id_Plan_Cuentas", $id_cuenta);
        $plan = $oItem->getData();
        unset($oItem);

        if ($plan["Estado"] === 'ACTIVO') {
            $oItem1 = new complex("Plan_Cuentas", "Id_Plan_Cuentas", $id_cuenta);
            $oItem1->Estado = 'INACTIVO';
            $oItem1->save();
            unset($oItem1);
        } elseif ($plan["Estado"] === 'INACTIVO') {
            $oItem2 = new complex("Plan_Cuentas", "Id_Plan_Cuentas", $id_cuenta);
            $oItem2->Estado = 'ACTIVO';
            $oItem2->save();
            unset($oItem2);
        } */

        PlanCuentas::where('Id_Plan_Cuentas', $id_cuenta)
            ->update(['Estado' => DB::raw("IF(Estado = 'ACTIVO', 'INACTIVO', 'ACTIVO')")]);


        $respuesta["msg"] = "Se ha modificado corretamente el estado del plan de cuentas";
        $respuesta["icon"] = "success";
        $respuesta["title"] = "¡Cambio exitoso!";

        return response()->json($respuesta);
    }

    public function comprobanteCuentas()
{
    $resultados = PlanCuentas::select('Id_Plan_Cuentas as value', DB::raw("CONCAT_WS(' ', Codigo, '-', Nombre) as text"))
        ->where('Banco', 'S')
        ->whereNotNull('Cod_Banco')
        ->get();

    return response()->json($resultados);
}

    public function filtrarCuentas(Request $request)
    {
        $tipo = $request->tipo;
        $match = $request->coincidencia;
        if ($tipo == 'pcga') {
            $resultados = PlanCuentas::where('Nombre', 'like', "%$match%")
                ->where('Movimiento', 'S')
                ->select(
                    'Id_Plan_Cuentas',
                    'Codigo',
                    'Nombre',
                    DB::raw('CONCAT(Codigo," - ", Nombre) AS Nombre_Cuenta')
                )
                ->orderBy('Nombre')->get();
        } else {
            $resultados = PlanCuentas::where('Nombre_Niif', 'like', "%$match%")
                ->where('Movimiento', 'S')
                ->select(
                    'Id_Plan_Cuentas',
                    'Codigo_Niif',
                    'Nombre_Niif',
                    DB::raw('CONCAT(Codigo_Niif," - ", Nombre_Niif) AS Nombre_Cuenta_Niif')
                )
                ->orderBy('Nombre_Niif')->get();
        }


        return response()->json($resultados);
    }



    public function listarBancos()
    {
        $bancos = Bank::orderBy('name', 'ASC')->get();

        return response()->json($bancos);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        $datos = isset($_REQUEST['Datos']) ? $_REQUEST['Datos'] : false;
        $datos = json_decode($datos, true);
        unset($datos['Cuenta_Padre_Nombre']);
        //dd($datos);
        $guardar = true;
        $oItem = '';
        if (isset($datos['Id_Plan_Cuentas']) && $datos['Id_Plan_Cuentas'] != '') {
            $oItem = PlanCuentas::find($datos['Id_Plan_Cuentas']);
        } else {
            $oItem = new PlanCuentas;
        }
        foreach ($datos as $index => $value) {
            if ($index == 'Codigo' || $index == 'Codigo_Niif') {
                if (!isset($datos['Id_Plan_Cuentas'])) { // Si no es un proceso de edición
                    if (PlanCuentas::where('Codigo', $value)->where('company_id', $datos["company_id"])->exists()) { // Si existe un PUC con el mismo código que no se guarde.
                        $guardar = false;
                        break;
                    }
                }
            }


            $oItem->$index = $value;
        }
        $oItem->company_id = $this->getCompany();
        $oItem->Codigo = $datos["Codigo_Niif"];
        $oItem->Nombre = $datos["Nombre_Niif"];
        $oItem->Tipo_P = $datos["Tipo_Niif"];
        $oItem->Movimiento = $datos['Tipo_Niif'] == 'AUXILIAR' ? 'S' : 'N';
        if ($guardar) {

            $oItem->save();
            $id_plan = $oItem->Id_Plan_Cuentas;
            $balance = AccountPlanBalance::where('account_plan_id', $id_plan)->first();
            if (!$balance) {
                AccountPlanBalance::create([
                    'account_plan_id' => $id_plan,
                    'balance' => 0
                ]);
            }
            if (strval($datos['Codigo_Padre']) === '110510') {
                PrettyCash::create([
                    'user_id' => Auth::id(),
                    'account_plan_id' => $id_plan,
                    'initial_balance' => 0,
                    'description' => $oItem->Nombre,
                    'status' => 'Inactiva'
                ]);
            }
            unset($oItem);

            if ($id_plan) {
                $resultado['mensaje'] = "Plan de cuenta registrado satisfactoriamente.";
                $resultado['tipo'] = "success";
            } else {
                $resultado['mensaje'] = "Ha ocurrido un error de conexion, comunicarse con el soporte tecnico.";
                $resultado['tipo'] = "error";
            }
        } else {
            $resultado['mensaje'] = "Ya existe un PUC con ese código.";
            $resultado['tipo'] = "error";
        }
        return response()->json($resultado);
    }

    function validarPUC($cod, $emp)
    {
        $query = "SELECT Id_Plan_Cuentas FROM Plan_Cuentas WHERE Codigo = '$cod' OR Codigo_Niif = '$cod' AND company_id=$emp";

        $oCon = new consulta();
        $oCon->setQuery($query);
        $resultado = $oCon->getData();
        unset($oCon);

        return $resultado || false;
    }

    public function validarNiveles()
    {
        $tipo_plan = $_REQUEST['Tipo_Plan'];
        $codigo = $_REQUEST['Codigo'];
        $tipo_puc = $_REQUEST['Tipo_Puc'];
        $nombre_nivel_superior = '';

        $codigo_validar = $this->getCodigoValidar($tipo_plan, $codigo);
        $campo_codigo = $tipo_puc == 'pcga' ? 'Codigo' : 'Codigo_Niif';

        $query = "SELECT Id_Plan_Cuentas FROM Plan_Cuentas WHERE $campo_codigo LIKE '$codigo_validar%'";

        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->setTipo('Multiple');
        $resultado = $oCon->getData();
        unset($oCon);

        $response = ["validacion" => 0, "nivel_superior" => $nombre_nivel_superior];

        if ($resultado) {
            $response['validacion'] = 1;
        }

        return response()->json($response);
    }

    function getCodigoValidar($tipo_plan, $codigo)
    {
        $nivel_superior = $this->getNivelSuperiorLength($tipo_plan);

        $codigo = substr($codigo, 0, $nivel_superior);

        return $codigo;
    }

    function getNivelSuperiorLength($tipo_plan)
    {

        $nombre_nivel_superior = '';

        $nombres_niveles = [
            "1" => "Grupo",
            "2" => "Clase",
            "4" => "Cuenta",
            "6" => "Subcuenta"
        ];

        $niveles_superior = [
            "auxiliar" => 6,
            "subcuenta" => 4,
            "cuenta" => 2,
            "clase" => 1
        ];

        $nombre_nivel_superior = $nombres_niveles[$niveles_superior[$tipo_plan]];

        return $niveles_superior[$tipo_plan];
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PlanCuentas  $planCuentas
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        $id_cuenta = (isset($_REQUEST['id_cuenta']) && $_REQUEST['id_cuenta'] != "") ? $_REQUEST['id_cuenta'] : '';

        $respuesta = array();
        $http_response = new HttpResponse();

        if ($id_cuenta == '') {
            $http_response->SetRespuesta(1, 'Detalle', 'No se envio el plan de cuenta para proceder, contacte con el administrador!');
            $respuesta = $http_response->GetRespuesta();
            return response()->json($respuesta);
        }

        $query = 'SELECT
        pc.*, (SELECT name FROM banks WHERE id = pc.Cod_Banco) AS Nombre_Banco, pc2.Nombre as Cuenta_Padre_Nombre
        FROM Plan_Cuentas as pc
        LEFT JOIN Plan_Cuentas as pc2 ON pc.Codigo_Padre = pc2.Codigo_Niif
        WHERE pc.Id_Plan_Cuentas = ' . $id_cuenta;

        $oCon = new consulta();
        $oCon->setQuery($query);
        $plan_cuenta = $oCon->getData();
        unset($oCon);

        $http_response->SetDatosRespuesta($plan_cuenta);
        $http_response->SetRespuesta(0, 'Detalle', 'Operación exitosa!');
        $respuesta = $http_response->GetRespuesta();
        return response()->json($respuesta);
    }

    public function getListaCuentasContables()
    {
        $ng_select = true;

        if ($ng_select) {
            $cuentas = PlanCuentas::select('Id_Plan_Cuentas as value', DB::raw("CONCAT(Codigo, ' - ', Nombre) AS Nombre"))
                ->where('Estado', 'Activo')
                ->where('Movimiento', 'S')
                ->get();
        } else {
            $cuentas = [];
        }

        return response()->json(['success' => true, 'data' => $cuentas]);
    }

    public function listaCuentas()
    {
        $company = $this->getCompany();

        $query = 'SELECT PC.Id_Plan_Cuentas,
                 PC.Id_Plan_Cuentas AS Id,
                 PC.Codigo,
                 PC.Codigo AS Codigo_Cuenta,
                 CONCAT(PC.Nombre," - ",PC.Codigo) as Codigo,
                 CONCAT(PC.Codigo," - ",PC.Nombre) as Nombre,
                 PC.Centro_Costo
                 FROM Plan_Cuentas PC
                 WHERE CHAR_LENGTH(PC.Codigo)>5 AND PC.Movimiento = "S"
                 AND PC.company_id=' . $company;

        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->setTipo('Multiple');
        $resultado['Activo'] = $oCon->getData();
        unset($oCon);

        $query = 'SELECT PC.Id_Plan_Cuentas,
                PC.Codigo AS Codigo_Cuenta,
                CONCAT(PC.Nombre," - ",PC.Codigo) as Codigo
                FROM Plan_Cuentas PC
                WHERE CHAR_LENGTH(PC.Codigo)>5 AND PC.Movimiento = "S"
                AND PC.company_id=' . $company;

        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->setTipo('Multiple');
        $resultado['Pasivo'] = $oCon->getData();
        unset($oCon);

        return response()->json($resultado);
    }
}
