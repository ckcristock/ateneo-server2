<?php

namespace App\Http\Controllers;

use App\Http\Services\PaginacionData;
use App\Http\Services\QueryBaseDatos;
use App\Http\Services\Utility;
use App\Models\DocInventarioFisicoPunto;
use App\Models\InventarioFisicoPuntoNuevo;
use App\Models\Person;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class InventarioFisicoPuntoNuevoController extends Controller
{
    use ApiResponser;

    public function documentosIniciados(Request $request)
    {
       $inv = DocInventarioFisicoPunto::select(
                'Doc_Inventario_Fisico_Punto.Id_Doc_Inventario_Fisico_Punto',
                'Doc_Inventario_Fisico_Punto.Funcionario_Digita AS Id_Funcionario_Digita',
                'Doc_Inventario_Fisico_Punto.Funcionario_Cuenta AS Id_Funcionario_Cuenta',
                'Doc_Inventario_Fisico_Punto.Fecha_Inicio',
                'Doc_Inventario_Fisico_Punto.Estado',
                'Doc_Inventario_Fisico_Punto.Id_Estiba',
                'Estiba.Nombre AS Estiba',
                'Punto_Dispensacion.Nombre AS Punto_Dispensacion',
                'FuncionarioD.first_name AS Funcionario_Digita_Nombres',
                'FuncionarioD.second_surname AS Funcionario_Digita_Apellidos',
                'FuncionarioC.first_name AS Funcionario_Cuenta_Nombres',
                'FuncionarioC.second_surname AS Funcionario_Cuenta_Apellidos'
            )
            ->join('Estiba', 'Estiba.Id_Estiba', '=', 'Doc_Inventario_Fisico_Punto.Id_Estiba')
            ->join('Punto_Dispensacion', 'Punto_Dispensacion.Id_Punto_Dispensacion', '=', 'Estiba.Id_Punto_Dispensacion')
            ->join('people AS FuncionarioD', 'FuncionarioD.identifier', '=', 'Doc_Inventario_Fisico_Punto.Funcionario_Digita')
            ->join('people AS FuncionarioC', 'FuncionarioC.identifier', '=', 'Doc_Inventario_Fisico_Punto.Funcionario_Cuenta')
            ->whereNotIn('Doc_Inventario_Fisico_Punto.Estado', ['Terminado'])
            ->get();

        
    
        if($inv->isNotEmpty()){
            $funcContador = Person::where('identifier', $inv[0]['Id_Funcionario_Cuenta'])->first();
            $funcDigitador = person::where('identifier', $inv[0]['Id_Funcionario_Digita'])->first();
    
            $resultado['tipo'] = "success";
            $resultado['documentos'] = $inv;
            $resultado['func_contador'] = $funcContador;
            $resultado['func_digitador'] = $funcDigitador;
        } else {
            $resultado['tipo'] = "error";
        }
    
        return $this->success($resultado);
    }
    

    public function documentosTerminados(Request $request)
{

    $condicion = $this->setCondiciones($request->all());
    $query = InventarioFisicoPuntoNuevo::select('Inventario_Fisico_Punto_Nuevo.*', 'B.Nombre as Nombre_Bodega', 'G.Nombre as Nombre_Grupo', 'F.full_name as Nombre_Funcionario_Autorizo')
    ->join('Grupo_Estiba as G', 'G.Id_Grupo_Estiba', '=', 'Inventario_Fisico_Punto_Nuevo.Id_Grupo_Estiba')
    ->join('Punto_Dispensacion as B', 'B.Id_Punto_Dispensacion', '=', 'Inventario_Fisico_Punto_Nuevo.Id_Punto_Dispensacion')
    ->join('people as F', 'F.identifier', '=', 'Inventario_Fisico_Punto_Nuevo.Funcionario_Autoriza');

    if (!empty($condicion)) {
        $query->whereRaw($condicion);
    }

    $direccionamientos = $query->orderByDesc('Id_Inventario_Fisico_Punto_Nuevo')
    ->paginate(Request()->get('pageSize', 10), ['*'], 'page', Request()->get('page', 1));
        return $this->success($direccionamientos);
}

public function setCondiciones($req)
{
    $condicion = '';

    if (isset($req['grupo']) && $req['grupo']) {
        $condicion .= ($condicion != "" ? " AND " : "") . "Inventario_Fisico_Punto_Nuevo.Id_Grupo_Estiba='{$req['grupo']}'";
    }

    if (isset($req['punto']) && $req['punto']) {
        $condicion .= ($condicion != "" ? " AND " : "") . "Inventario_Fisico_Punto_Nuevo.Id_Punto_Dispensacion='{$req['punto']}'";
    }

    if (isset($req['fechas']) && $req['fechas']) {
        $fechas_separadas = (new Utility())->separarFechas($req['fechas']);
        $condicion .= ($condicion != "" ? " AND " : "") . "DATE(Inventario_Fisico_Punto_Nuevo.Fecha) BETWEEN '{$fechas_separadas[0]}' AND '{$fechas_separadas[1]}'";
    }

    return $condicion;
}


}
