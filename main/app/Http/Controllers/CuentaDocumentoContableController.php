<?php

namespace App\Http\Controllers;

use App\Models\CuentaDocumentoContable;
use Illuminate\Http\Request;
use App\Http\Services\consulta;
use App\Traits\ApiResponser;
use Illuminate\Support\Facades\DB;
use App\Models\Comprobante;
use App\Models\DocumentoContable;


class CuentaDocumentoContableController extends Controller
{
    use ApiResponser;
    public function listaNotasCartera()
    {
        $condicion = $this->getStrCondicionsNotasCartera();

        $query = DB::table('Cuenta_Documento_Contable as CDC')
            ->select(
                'CDC.Id_Documento_Contable',
                'NC.Estado',
                DB::raw("DATE_FORMAT(NC.Fecha_Documento, '%d/%m/%Y') AS Fecha"),
                'NC.Codigo',
                'NC.Beneficiario',
                DB::raw("(CASE NC.Tipo_Beneficiario
                WHEN 'Cliente' THEN (SELECT IF(Nombre IS NULL OR Nombre = '', CONCAT_WS(' ', Primer_Nombre, Segundo_Nombre, Primer_Apellido, Segundo_Apellido), Nombre) FROM Cliente WHERE Id_Cliente = NC.Beneficiario)
                WHEN 'Proveedor' THEN (SELECT IF(Nombre IS NULL OR Nombre = '', CONCAT_WS(' ', Primer_Nombre, Segundo_Nombre, Primer_Apellido, Segundo_Apellido), Nombre) FROM Proveedor WHERE Id_Proveedor = NC.Beneficiario)
                WHEN 'Funcionario' THEN (SELECT CONCAT_WS(' ', first_name, first_surname) FROM people WHERE id = NC.Beneficiario)
                END) AS Tercero"),
                'NC.Concepto',
                DB::raw("GROUP_CONCAT(CDC.Cheque SEPARATOR ' | ') AS Cheques"),
                DB::raw("SUM(CDC.Debito) AS Total_Debe_PCGA"),
                DB::raw("SUM(CDC.Credito) AS Total_Haber_PCGA"),
                DB::raw("SUM(CDC.Deb_Niif) AS Total_Debe_NIIF"),
                DB::raw("SUM(CDC.Cred_Niif) AS Total_Haber_NIIF"),
                DB::raw("(SELECT CONCAT_WS(' ', first_name, first_surname) FROM people WHERE id = NC.Identificacion_Funcionario) AS Funcionario")
            )
            ->join('Documento_Contable as NC', 'NC.Id_Documento_Contable', '=', 'CDC.Id_Documento_Contable')
            ->where('NC.Tipo', 'Nota Cartera')
            ->groupBy('CDC.Id_Documento_Contable');

        if (request()->filled('tercero')) {
            $tercero = request('tercero');
            $query->havingRaw("Beneficiario LIKE '%$tercero%' OR Tercero LIKE '%$tercero%'");
        }

        $resultado = $query->orderBy('NC.Fecha_Registro', 'DESC')->paginate(20);

        $response['Notas'] = $resultado;
        $response['numReg'] = $resultado->total();

        return $this->success($response);
    }

    function getStrCondicionsNotasCartera()
    {
        $condicion = '';

        if (request()->filled('cod')) {
            $condicion .= " AND NC.Codigo LIKE '%" . request('cod') . "%'";
        }

        if (request()->filled('fecha')) {
            $fecha_inicio = trim(explode(' - ', request('fecha'))[0]);
            $fecha_fin = trim(explode(' - ', request('fecha'))[1]);
            $condicion .= " AND (DATE(NC.Fecha_Documento) BETWEEN '$fecha_inicio' AND '$fecha_fin')";
        }

        /* if (request()->filled('tercero')) {
            $condicion .= " AND NC.Beneficiario = '" . request('tercero') . "'";
        } */
        if (request()->filled('est')) {
            $condicion .= " AND NC.Estado = '" . request('est') . "'";
        }

        return $condicion;
    }

    public function listaEgresos()
    {
        $query = DocumentoContable::select(
            'Documento_Contable.Id_Documento_Contable',
            DB::raw("DATE_FORMAT(Documento_Contable.Fecha_Documento, '%d/%m/%Y') AS Fecha"),
            'Documento_Contable.Codigo',
            'Documento_Contable.Beneficiario',
            DB::raw("(CASE Documento_Contable.Tipo_Beneficiario
            WHEN 'Cliente' THEN (SELECT IF(social_reason IS NULL OR social_reason = '', CONCAT_WS(' ', first_name, second_name, first_surname, second_surname), social_reason) FROM third_parties WHERE is_client = 1 AND id = Documento_Contable.Beneficiario)
            WHEN 'Proveedor' THEN (SELECT IF(social_reason IS NULL OR social_reason = '', CONCAT_WS(' ', first_name, second_name, first_surname, second_surname), social_reason) FROM third_parties WHERE is_supplier = 1 AND id = Documento_Contable.Beneficiario)
            WHEN 'Funcionario' THEN (SELECT CONCAT_WS(' ', first_name, first_surname) FROM people WHERE id = Documento_Contable.Beneficiario)
        END) AS Tercero"),
            DB::raw("(SELECT name FROM companies WHERE id = Documento_Contable.Id_Empresa) AS Empresa"),
            'Documento_Contable.Estado',
            'Documento_Contable.Concepto',
            DB::raw("GROUP_CONCAT(Cuenta_Documento_Contable.Cheque SEPARATOR ' | ') AS Cheques"),
            DB::raw("SUM(Cuenta_Documento_Contable.Debito) AS Total_Debe_PCGA"),
            DB::raw("SUM(Cuenta_Documento_Contable.Credito) AS Total_Haber_PCGA"),
            DB::raw("SUM(Cuenta_Documento_Contable.Deb_Niif) AS Total_Debe_NIIF"),
            DB::raw("SUM(Cuenta_Documento_Contable.Cred_Niif) AS Total_Haber_NIIF"),
            DB::raw("(SELECT CONCAT_WS(' ', first_name, first_surname) FROM people WHERE id = Documento_Contable.Identificacion_Funcionario) AS Funcionario")
        )
            ->join('Cuenta_Documento_Contable', 'Cuenta_Documento_Contable.Id_Documento_Contable', '=', 'Documento_Contable.Id_Documento_Contable')
            ->where('Documento_Contable.Tipo', 'Egreso');

        if (request()->filled('cli')) {
            $filtroCli = explode(' - ', request('cli')); 
            $beneficiario = $filtroCli[0];
            $tercero = $filtroCli[1] ?? ''; 

            $query->where(function ($q) use ($beneficiario, $tercero) {
                $q->where(function ($qq) use ($beneficiario, $tercero) {
                    $qq->where('Documento_Contable.Beneficiario', '=', $beneficiario);
                })
                    ->Where(function ($qq) use ($beneficiario, $tercero) {
                        $qq->where('Documento_Contable.Beneficiario', '=', $beneficiario)
                            ->whereRaw("(CASE Documento_Contable.Tipo_Beneficiario
                                WHEN 'Cliente' THEN (SELECT IF(social_reason IS NULL OR social_reason = '', CONCAT_WS(' ', first_name, second_name, first_surname, second_surname), social_reason) FROM third_parties WHERE is_client = 1 AND id = Documento_Contable.Beneficiario)
                                WHEN 'Proveedor' THEN (SELECT IF(social_reason IS NULL OR social_reason = '', CONCAT_WS(' ', first_name, second_name, first_surname, second_surname), social_reason) FROM third_parties WHERE is_supplier = 1 AND id = Documento_Contable.Beneficiario)
                                WHEN 'Funcionario' THEN (SELECT CONCAT_WS(' ', first_name, first_surname) FROM people WHERE id = Documento_Contable.Beneficiario)
                            END) = ?", [$tercero]);
                    });
            });
        }

        if (request()->filled('start_date') && request()->filled('end_date')) {
            $start_date = request('start_date');
            $end_date = request('end_date');
            $query->whereBetween(DB::raw('DATE(Documento_Contable.Fecha_Documento)'), [$start_date, $end_date]);
        }

        if (request()->filled('cod')) {
            $cod = request('cod');
            $query->where('Documento_Contable.Codigo', 'LIKE', "%$cod%");
        }

        if (request()->filled('cheque')) {
            $cheque = request('cheque');
            $query->where('Cuenta_Documento_Contable.Cheque', 'LIKE', "%$cheque%");
        }

        if (request()->filled('est')) {
            $est = request('est');
            $query->where('Documento_Contable.Estado', 'LIKE', $est);
        }


        $resultado = $query->groupBy('Documento_Contable.Id_Documento_Contable')
            ->paginate(request()->get('pageSize', 10), ['*'], 'page', request()->get('page', 1));

        return $this->success($resultado);
    }


    public function listaComprobantes()
    {
        $query = Comprobante::with('formaPago')
            ->leftJoin('third_parties as Cliente', function ($join) {
                $join->on('Comprobante.Id_Cliente', '=', 'Cliente.id')
                    ->where('Cliente.is_client', '=', true);
            })
            ->leftJoin('third_parties as Proveedor', function ($join) {
                $join->on('Comprobante.Id_Proveedor', '=', 'Proveedor.id')
                    ->where('Proveedor.is_supplier', '=', true);
            })
            ->when(request()->filled('cod'), function ($query) {
                $query->where('Comprobante.Codigo', 'like', '%' . request('cod') . '%');
            })
            ->when(request()->filled('tipo'), function ($query) {
                $query->whereHas('formaPago', function ($query) {
                    $query->where('Nombre', request('tipo'));
                });
            })
            ->when(request()->filled(['start_date', 'end_date']), function ($query) {
                $start_date = request('start_date');
                $end_date = request('end_date');
                $query->where('Comprobante.Fecha_Comprobante', '>=', $start_date)
                    ->where('Comprobante.Fecha_Comprobante', '<=', $end_date);
            })

            ->when(request()->filled('cli'), function ($query) {
                $query->where('Cliente.social_reason', 'like', '%' . request('cli') . '%');
            })
            ->when(request()->filled('est'), function ($query) {
                $query->where('Comprobante.Estado', 'like', request('est'));
            })
            ->when(request()->filled('tipo_comprobante'), function ($query) {
                $query->where('Comprobante.Tipo', ucwords(request('tipo_comprobante')));
            })
            ->orderBy('Comprobante.Fecha_Registro', 'DESC')
            ->paginate(request()->get('pageSize', 10), ['*'], 'page', request()->get('page', 1));

        return $this->success($query);
    }


}
