<?php

namespace App\Http\Controllers;

use App\Models\BorradorContabilidad;
use Illuminate\Http\Request;
use App\Http\Services\PaginacionData;
use App\Http\Services\HttpResponse;
use App\Http\Services\consulta;
use App\Http\Services\complex;
use App\Http\Services\Utility;
use App\Http\Services\Contabilizar;
use App\Http\Services\SystemConstant;
use App\Http\Services\QueryBaseDatos;

class BorradorContabilidadController extends Controller
{
    public function lista()
    {
        $tipo_comprobante = isset($_REQUEST['Tipo_Comprobante']) ? $_REQUEST['Tipo_Comprobante'] : false;
        $funcionario = isset($_REQUEST['Identificacion_Funcionario']) ? $_REQUEST['Identificacion_Funcionario'] : false;
        $resultado = [];

        if ($tipo_comprobante && $funcionario) {
            $query = "SELECT Id_Borrador_Contabilidad AS ID, CONCAT(BC.Codigo, ' | ', CONCAT_WS(' ',F.first_name,F.first_surname), ' | ', DATE_FORMAT(BC.created_at,'%d/%m/%Y %H:%i:%s')) AS Title FROM Borrador_Contabilidad BC INNER JOIN people F ON F.id = BC.Identificacion_Funcionario WHERE Estado = 'Activa' AND Tipo_Comprobante = '$tipo_comprobante' AND BC.Identificacion_Funcionario = $funcionario";

            $oCon = new consulta();
            $oCon->setQuery($query);
            $oCon->setTipo('Multiple');
            $resultado = $oCon->getData();
            unset($oCon);
        }

        return response()->json($resultado);
    }

    public function detalles()
    {
        $id = isset($_REQUEST['id']) ? $_REQUEST['id'] : false;
        $resultado = [];

        if ($id) {
            $query = "SELECT Id_Borrador_Contabilidad AS ID, Codigo, Datos FROM Borrador_Contabilidad WHERE Id_Borrador_Contabilidad = $id";

            $oCon = new consulta();
            $oCon->setQuery($query);
            $resultado = $oCon->getData();
            unset($oCon);
        }

        return response()->json($resultado);
    }

    public function guardar()
    {
        $datos = isset($_REQUEST['datos']) ? $_REQUEST['datos'] : false;
        $resultado = [];

        if ($datos) {
            $datos = json_decode($datos, true);
            $idBorrador = false;
            $oItem = null;

            if (isset($datos['Id_Borrador_Contabilidad']) && $datos['Id_Borrador_Contabilidad'] != '') {
                $oItem = new complex('Borrador_Contabilidad', 'Id_Borrador_Contabilidad', $datos['Id_Borrador_Contabilidad']);
            } else {
                $oItem = new complex('Borrador_Contabilidad', 'Id_Borrador_Contabilidad');
            }

            foreach ($datos as $index => $value) {
                if ($index != 'Id_Borrador_Contabilidad') {
                    $valor = $index == 'Datos' ? json_encode($value) : $value;
                    $oItem->$index = $valor;
                }
            }
            $oItem->save();

            $idBorrador = (isset($datos['Id_Borrador_Contabilidad']) && $datos['Id_Borrador_Contabilidad'] != '') ? $datos['Id_Borrador_Contabilidad'] : $oItem->getId();
            unset($oItem);

            if ($idBorrador) {
                $resultado['Id_Borrador'] = $idBorrador;
                $resultado['status'] = 202;
            } else {
                $resultado['status'] = 500;
            }
        } else {
            $resultado['status'] = 500;
        }

        return response()->json($resultado);
    }
}
