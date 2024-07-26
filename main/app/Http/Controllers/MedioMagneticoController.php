<?php

namespace App\Http\Controllers;

use App\Models\MedioMagnetico;
use Illuminate\Http\Request;
use App\Http\Services\consulta;

class MedioMagneticoController extends Controller
{

    public function lista(Request $request)
    {
        $query = MedioMagnetico::select('Medio_Magnetico.Id_Medio_Magnetico AS Id', 'Medio_Magnetico.Periodo', 'Medio_Magnetico.Codigo_Formato', 'Medio_Magnetico.Nombre_Formato', 'Medio_Magnetico.Tipo_Exportacion', 'Medio_Magnetico.Tipo_Columna', 'companies.name as Empresa')
            ->leftJoin('companies', 'companies.id', '=', 'Medio_Magnetico.Id_Empresa')
            ->where('Estado', 'Activo');
        $request->filled('Tipo') ?
            $query->where('Tipo_Medio_Magnetico', 'Especial') :
            $query->where('Tipo_Medio_Magnetico', 'Basico');
        $lista = $query->get();
        return response()->json($lista);

    }

    public function detalles()
{
    $id = request()->input('id');

    $detalles = [];

    if ($id) {
        $resultado = MedioMagnetico::where('Estado', 'Activo')
                                    ->where('Id_Medio_Magnetico', $id)
                                    ->select('Id_Medio_Magnetico', 'Periodo', 'Codigo_Formato', 'Nombre_Formato', 'Tipo_Exportacion', 'Detalles', 'Tipos', 'Tipo_Medio_Magnetico', 'Tipo_Columna', 'Columna_Principal')
                                    ->first();

        if ($resultado) {
            $detalles['encabezado'] = $resultado;
            $detalles['cuentas'] = $resultado->Detalles;
            $detalles['tipos'] = $resultado->Tipos;
        }
    }

    return response()->json($detalles);
}

    public function formatosEspeciales()
    {
        $query = "SELECT Id_Medio_Magnetico AS value, CONCAT(Codigo_Formato,' - ',Nombre_Formato) AS label FROM Medio_Magnetico WHERE Estado = 'Activo' AND Tipo_Medio_Magnetico = 'Especial'";

        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->setTipo('Multiple');
        $lista = $oCon->getData();
        unset($oCon);

        return response()->json($lista);
    }
}
