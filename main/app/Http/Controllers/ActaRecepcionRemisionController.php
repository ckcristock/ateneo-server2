<?php

namespace App\Http\Controllers;

use App\Models\ActaRecepcion;
use App\Traits\ApiResponser;
use App\Models\ActaRecepcionRemision;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActaRecepcionRemisionController extends Controller
{
    use ApiResponser;
    public function indexPaginate(Request $request)
    {
        $company_id = getCompanyWorkedId(); 
        $id_punto_dispensacion = Person::find(auth()->user()->person_id)->dispensing_point_id;
    
        $cod = $request->input('cod', '');
        $codr = $request->input('codr', '');
        $fecha = $request->input('fecha', '');
    
        $fecha_inicio = null;
        $fecha_fin = null;
    
        if ($fecha) {
            $fechas = explode(' - ', $fecha);
            $fecha_inicio = trim($fechas[0]);
            $fecha_fin = trim($fechas[1]);
        }
    
        // Primera parte de la consulta para Acta_Recepcion_Remision
        $remisiones = ActaRecepcionRemision::select(
            'Acta_Recepcion_Remision.Id_Acta_Recepcion_Remision as Id_Acta',
            'Acta_Recepcion_Remision.Codigo',
            'Acta_Recepcion_Remision.Fecha',
            'people.image as Imagen',
            'Remision.Codigo as Codigo_Remision'
        )
            ->join('people', 'people.identifier', '=', 'Acta_Recepcion_Remision.Identificacion_Funcionario')
            ->join('Remision', 'Remision.Id_Remision', '=', 'Acta_Recepcion_Remision.Id_Remision')
            ->where('Id_Punto_Dispensacion', $id_punto_dispensacion)
            ->when($cod, function ($query) use ($cod) {
                return $query->where('Acta_Recepcion_Remision.Codigo', 'like', '%' . $cod . '%');
            })
            ->when($codr, function ($query) use ($codr) {
                return $query->where('Remision.Codigo', 'like', '%' . $codr . '%');
            })
            ->when($fecha_inicio && $fecha_fin, function ($query) use ($fecha_inicio, $fecha_fin) {
                return $query->whereBetween('Acta_Recepcion_Remision.Fecha', [$fecha_inicio, $fecha_fin]);
            });
    
        // Segunda parte de la consulta para Acta_Recepcion
        $ordenesCompra = ActaRecepcion::select(
            'ARC.Id_Acta_Recepcion as Id_Acta',
            'ARC.Codigo',
            'ARC.Fecha_Creacion as Fecha',
            'F.image as Imagen',
            DB::raw('NULL as Codigo_Remision')
        )
            ->from('Acta_Recepcion as ARC')
            ->leftJoin('people as F', 'F.identifier', '=', 'ARC.Identificacion_Funcionario')
            ->leftJoin('Orden_Compra_Nacional as OCN', 'OCN.Id_Orden_Compra_Nacional', '=', 'ARC.Id_Orden_Compra_Nacional')
            ->leftJoin('Bodega_Nuevo as B', 'B.Id_Bodega_Nuevo', '=', 'ARC.Id_Bodega_Nuevo')
            ->join('third_parties as P', 'P.id', '=', 'ARC.Id_Proveedor')
            ->where('ARC.tipo_compra', 'solicitud')
            ->where('ARC.Id_Punto_Dispensacion', $id_punto_dispensacion)
            ->where('ARC.company_id', $company_id)
            ->when($cod, function ($query) use ($cod) {
                return $query->where('ARC.Codigo', 'like', '%' . $cod . '%');
            })
            ->when($fecha_inicio && $fecha_fin, function ($query) use ($fecha_inicio, $fecha_fin) {
                return $query->whereBetween('ARC.Fecha_Creacion', [$fecha_inicio, $fecha_fin]);
            });
    
        // Combinar las dos consultas
        $query = $remisiones->union($ordenesCompra);
    
        return $this->success(
            $query->orderByDesc('Fecha')->paginate($request->get('pageSize', 5), ['*'], 'page', $request->get('page', 1))
        );
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
