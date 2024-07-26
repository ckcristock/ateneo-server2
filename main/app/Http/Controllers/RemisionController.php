<?php

namespace App\Http\Controllers;

use App\Models\Remision;
use Illuminate\Http\Request;
use App\Http\Services\consulta;
use App\Models\BodegaNuevo;
use App\Models\Borrador;
use App\Models\DevolucionCompra;
use App\Models\FuncionarioBodegaNuevo;
use App\Models\ListaGanancia;
use App\Models\NoConforme;
use App\Models\People;
use App\Models\ThirdParty;
use App\Traits\ApiResponser;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\descargaExcelExport;
use App\Http\Services\complex;
use App\Http\Services\HttpResponse;
use App\Http\Services\QueryBaseDatos;
use App\Http\Services\Utility;
use App\Models\City;
use App\Models\CompanyConfiguration;
use App\Models\Configuracion;
use App\Models\DepartamentoCliente;
use App\Models\Municipality;
use App\Models\Product;
use App\Models\WinningList;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class RemisionController extends Controller
{

    use ApiResponser;
    public function datosIniciales(Request $request)
    {
        $id = $request->input('id');

        $bodega = FuncionarioBodegaNuevo::select('B.Nombre as text', DB::raw('CONCAT("B-", B.Id_Bodega_Nuevo) as value'))
            ->join('Bodega_Nuevo as B', 'Funcionario_Bodega_Nuevo.Id_Bodega_Nuevo', '=', 'B.Id_Bodega_Nuevo')
            ->where('Funcionario_Bodega_Nuevo.Identificacion_Funcionario', $id)
            ->get();

        $punto = People::select(DB::raw('CONCAT("P-", people.dispensing_point_id) as value'), 'people.first_name as text')
            ->where('people.identifier', $id)
            ->get();

        $lganancia = WinningList::select('winning_lists.name as Nombre', DB::raw('CONCAT("L-", winning_lists.id) as Id_Lista_Ganancia'))
            ->get();

        $clientes = ThirdParty::select(
            DB::raw('IFNULL(social_reason,concat(first_name," ",first_surname)) as text'),
            DB::raw('CONCAT("C-",id) as value'),
            'winning_list_id as Id_Lista_Ganancia'
        )->where('state', 'Activo')
            ->where('is_client', 1)
            ->orderBy('text')
            ->get();

        $resultado["Bodega"] = $bodega;
        $resultado["Punto"] = $punto;
        $resultado["Lista"] = $lganancia;
        $resultado["Clientes"] = $clientes;

        return response()->json($resultado);
    }


    public function detalleAlistamiento(Request $request)
    {
        $condiciones = [];

        if ($request->filled('cod')) {
            $condiciones[] = ['Codigo', 'like', '%' . $request->input('cod') . '%'];
        }
        if ($request->filled('tipo')) {
            $condiciones[] = ['Tipo', '=', $request->input('tipo')];
        }
        if ($request->filled('origen')) {
            $condiciones[] = ['Nombre_Origen', 'like', '%' . $request->input('origen') . '%'];
        }
        if ($request->filled('destino')) {
            $condiciones[] = ['Nombre_Destino', 'like', '%' . $request->input('destino') . '%'];
        }
        if ($request->filled('est')) {
            $condiciones[] = ['Estado', '=', $request->input('est')];
        }
        if ($request->filled('fecha')) {
            $fechas = explode(' - ', $request->input('fecha'));
            $fecha_inicio = trim($fechas[0]);
            $fecha_fin = trim($fechas[1]);
            $condiciones[] = ['Fecha', '>=', $fecha_inicio];
            $condiciones[] = ['Fecha', '<=', $fecha_fin];
        }

        $estadoAlistamiento = $request->filled('fases') ? $request->input('fases') : 2;
        $condicionPrincipal = 'Estado_Alistamiento = ' . $estadoAlistamiento;

        $remisiones = DB::table('Remision')->select(
            'Id_Remision',
            'Codigo',
            'Tipo',
            'Tipo_Origen',
            'Tipo_Destino',
            'Tipo_Bodega',
            'Guia',
            'Empresa_Envio',
            'Estado_Alistamiento',
            'Fecha',
            'Id_Origen',
            'Id_Destino',
            'Nombre_Origen',
            'Nombre_Destino',
            'Estado'
        );

        $devoluciones = DB::table('Devolucion_Compra')->select(
            'Id_Devolucion_Compra AS Id_Remision',
            'Codigo',
            DB::raw('"Devolucion" AS Tipo'),
            DB::raw('"Bodega_Nuevo" AS Tipo_Origen'),
            DB::raw('"Proveedor" AS Tipo_Destino'),
            DB::raw('"Medicamentos" AS Tipo_Bodega'),
            'Guia',
            'Empresa_Envio',
            'Estado_Alistamiento',
            'Fecha',
            'Id_Bodega_nuevo AS Id_Origen',
            'Id_Proveedor AS Id_Destino',
            DB::raw('(SELECT Nombre FROM Bodega_Nuevo WHERE Id_Bodega_Nuevo = Devolucion_Compra.Id_Bodega_nuevo) AS Nombre_Origen'),
            DB::raw('(SELECT first_name FROM third_parties WHERE id = Devolucion_Compra.Id_Proveedor) AS Nombre_Destino'),
            'Estado'
        )
            ->unionAll($remisiones)
            ->orderBy('Fecha', 'desc');


        $resultado = DB::table(DB::raw("({$devoluciones->toSql()}) as devoluciones"))
            ->mergeBindings($devoluciones)
            ->whereRaw($condicionPrincipal)
            ->where($condiciones)
            ->paginate($request->input('pageSize', 10), ['*'], 'page', $request->input('page', 1));

        foreach ($resultado as $item) {
            // Cargar el origen
            switch ($item->Tipo_Origen) {
                case 'Bodega':
                case 'Bodega_Nuevo':
                    $item->origen = BodegaNuevo::find($item->Id_Origen);
                    break;
                case 'Cliente':
                    $item->origen = ThirdParty::find($item->Id_Origen);
                    break;
                default:
                    $item->origen = null;
            }

            // Cargar el destino
            switch ($item->Tipo_Destino) {
                case 'Bodega':
                case 'Bodega_Nuevo':
                    $item->destino = BodegaNuevo::find($item->Id_Destino);
                    break;
                case 'Cliente':
                    $item->destino = ThirdParty::find($item->Id_Destino);
                    break;
                default:
                    $item->destino = null;
            }

            // Obtener el nombre del destino
            if ($item->destino) {
                $item->NombreDestino = ($item->Tipo_Destino == 'Cliente') ? $item->destino->NombreCompleto : $item->destino->Nombre;
            } else {
                $item->NombreDestino = '';
            }

            // Obtener el nombre del origen
            if ($item->origen) {
                $item->NombreOrigen = ($item->Tipo_Origen == 'Cliente') ? $item->origen->NombreCompleto : $item->origen->Nombre;
            } else {
                $item->NombreOrigen = '';
            }
        }

        return $this->success($resultado);
    }

    public function detalleFase1(Request $request)
    {
        $codigo1 = $request->input('codigo1');
        $origen1 = $request->input('origen1');
        $destino1 = $request->input('destino1');
        $funcionario = $request->input('funcionario', '');

        // Base query for remisiones
        $remisionesQuery = Remision::selectRaw('Remision.Id_Contrato, Remision.Codigo, Remision.Nombre_Origen, Remision.Nombre_Destino, Remision.Prioridad, Remision.Id_Remision, people.image as Imagen, "Remision" AS Tipo, Remision.Tipo_Bodega')
            ->join('people', 'people.identifier', '=', 'Remision.Identificacion_Funcionario')
            ->where(function ($query) use ($funcionario) {
                $query->where('Tipo_Origen', 'Bodega')
                    ->where('Estado_Alistamiento', 0)
                    ->where('Estado', 'Pendiente')
                    ->where(function ($query) use ($funcionario) {
                        $query->where('Fase_1', 0)->orWhere('Fase_1', $funcionario);
                    });
            })
            ->where(function ($query) use ($codigo1, $origen1, $destino1) {
                if ($codigo1) {
                    $query->where('Codigo', 'like', "%$codigo1%");
                }
                if ($origen1) {
                    $query->where('Nombre_Origen', 'like', "%$origen1%");
                }
                if ($destino1) {
                    $query->where('Nombre_Destino', 'like', "%$destino1%");
                }
            })
            ->where(function ($query) {
                $query->whereRaw('Tipo_Destino = "Punto_Dispensacion" AND Id_Destino IN (SELECT Id_Punto_Dispensacion FROM Punto_Dispensacion)')
                    ->orWhereRaw('Tipo_Destino IN ("Cliente", "Bodega", "Contrato")');
            });

        // Base query for devoluciones
        $devolucionesQuery = DevolucionCompra::with('proveedor')
            ->selectRaw('0, Devolucion_Compra.Codigo, Bodega_Nuevo.Nombre AS Nombre_Origen, IFNULL(third_parties.social_reason, CONCAT(third_parties.first_name, " " , third_parties.first_surname)) AS Nombre_Destino,
                "1" AS Prioridad, Devolucion_Compra.Id_Devolucion_Compra AS Id_Remision, people.image as Imagen, "Devolucion" AS Tipo')
            ->where('Devolucion_Compra.Estado', 'Activa')
            ->where('Devolucion_Compra.Estado_Alistamiento', 0)
            ->join('Bodega_Nuevo', 'Devolucion_Compra.Id_Bodega_Nuevo', '=', 'Bodega_Nuevo.Id_Bodega_Nuevo')
            ->join('people', 'people.identifier', '=', 'Devolucion_Compra.Identificacion_Funcionario')
            ->join('third_parties', 'third_parties.id', '=', 'Devolucion_Compra.Id_Proveedor')
            ->where(function ($query) use ($codigo1) {
                if ($codigo1) {
                    $query->where('Codigo', 'like', "%$codigo1%");
                }
            })
            ->where(function ($query) use ($origen1) {
                if ($origen1) {
                    $query->where('Bodega_Nuevo.Nombre', 'like', "%$origen1%");
                }
            })
            ->where(function ($query) use ($destino1) {
                if ($destino1) {
                    $query->where('Bodega_Nuevo.Nombre', 'like', "%$destino1%");
                }
            });

        // Get the total count of records
        $totalRemisiones = $remisionesQuery->count();
        $totalDevoluciones = $devolucionesQuery->count();
        $total = $totalRemisiones + $totalDevoluciones;

        // Apply pagination
        $perPage = $request->input('pageSize', 10);
        $page = $request->input('page', 1);
        $skip = ($page - 1) * $perPage;

        $remisiones = $remisionesQuery->skip($skip)->take($perPage)->get();
        $devoluciones = $devolucionesQuery->skip($skip)->take($perPage)->get();

        // Combine results
        $combined = new Collection($remisiones->merge($devoluciones)->all());

        // Create a paginator for the combined results
        $resultados = new LengthAwarePaginator(
            $combined,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return $this->success($resultados);
    }

    public function detalleFase2(Request $request)
    {
        $codigo2 = $request->input('codigo2');
        $origen2 = $request->input('origen2');
        $destino2 = $request->input('destino2');
        $funcionario = $request->input('funcionario', '');

        $bodegas_funcionarios = FuncionarioBodegaNuevo::where('Identificacion_Funcionario', $funcionario)
            ->pluck('Id_Bodega_Nuevo');

        // Base query for remisiones
        $remisionesQuery = Remision::selectRaw('Remision.Id_Contrato, Remision.Codigo, Remision.Nombre_Origen, Remision.Nombre_Destino, Remision.Prioridad, Remision.Id_Remision, people.image as Imagen, "Remision" AS Tipo, Remision.Tipo_Bodega')
            ->leftJoin('people', 'people.identifier', '=', 'Remision.Identificacion_Funcionario')
            ->where(function ($query) use ($funcionario, $bodegas_funcionarios) {
                $query->where('Tipo_Origen', 'Bodega')
                    ->where('Estado_Alistamiento', 1)
                    ->where('Estado', 'Pendiente')
                    ->where(function ($query) use ($funcionario, $bodegas_funcionarios) {
                        $query->where('Fase_2', 0)->orWhere('Fase_2', $funcionario)
                            ->whereIn('Id_Origen', $bodegas_funcionarios);
                    });
            })
            ->where(function ($query) use ($codigo2, $origen2, $destino2) {
                if ($codigo2) {
                    $query->where('Codigo', 'like', "%$codigo2%");
                }
                if ($origen2) {
                    $query->where('Nombre_Origen', 'like', "%$origen2%");
                }
                if ($destino2) {
                    $query->where('Nombre_Destino', 'like', "%$destino2%");
                }
            })
            ->where(function ($query) {
                $query->whereRaw('Tipo_Destino = "Punto_Dispensacion" AND Id_Destino IN (SELECT Id_Punto_Dispensacion FROM Punto_Dispensacion)')
                    ->orWhereRaw('Tipo_Destino IN ("Cliente", "Bodega", "Contrato")')
                    ->orWhereRaw('Tipo_Bodega = "REFRIGERADOS"');
            });

        // Base query for devoluciones
        $devolucionesQuery = DevolucionCompra::with('proveedor')
            ->selectRaw('0, Devolucion_Compra.Codigo, Bodega_Nuevo.Nombre AS Nombre_Origen, IFNULL(third_parties.social_reason, CONCAT(third_parties.first_name, " " , third_parties.first_surname)) AS Nombre_Destino,
                "1" AS Prioridad, Devolucion_Compra.Id_Devolucion_Compra AS Id_Remision, people.image as Imagen, "Devolucion" AS Tipo')
            ->where('Devolucion_Compra.Estado', 'Activa')
            ->where('Devolucion_Compra.Estado_Alistamiento', 1)
            ->join('Bodega_Nuevo', 'Devolucion_Compra.Id_Bodega_Nuevo', '=', 'Bodega_Nuevo.Id_Bodega_Nuevo')
            ->join('people', 'people.identifier', '=', 'Devolucion_Compra.Identificacion_Funcionario')
            ->join('third_parties', 'third_parties.id', '=', 'Devolucion_Compra.Id_Proveedor')
            ->where(function ($query) use ($codigo2) {
                if ($codigo2) {
                    $query->where('Codigo', 'like', "%$codigo2%");
                }
            })
            ->where(function ($query) use ($origen2) {
                if ($origen2) {
                    $query->where('Bodega_Nuevo.Nombre', 'like', "%$origen2%");
                }
            });

        // Get the total count of records
        $totalRemisiones = $remisionesQuery->count();
        $totalDevoluciones = $devolucionesQuery->count();
        $total = $totalRemisiones + $totalDevoluciones;

        // Apply pagination
        $perPage = $request->input('pageSize', 10);
        $page = $request->input('page', 1);
        $skip = ($page - 1) * $perPage;

        $remisiones = $remisionesQuery->skip($skip)->take($perPage)->get();
        $devoluciones = $devolucionesQuery->skip($skip)->take($perPage)->get();

        // Combine results
        $combined = new Collection($remisiones->merge($devoluciones)->all());

        // Create a paginator for the combined results
        $resultados = new LengthAwarePaginator(
            $combined,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return $this->success($resultados);
    }

    public function remisiones(Request $request)
    {
        $condiciones = [];
        $parametros = [];

        if ($request->filled('cod')) {
            $condiciones[] = "Codigo LIKE ?";
            $parametros[] = '%' . $request->input('cod') . '%';
        }

        if ($request->filled('tipo')) {
            $condiciones[] = "Tipo = ?";
            $parametros[] = $request->input('tipo');
        }

        if ($request->filled('origen')) {
            $condiciones[] = "Nombre_Origen LIKE ?";
            $parametros[] = '%' . $request->input('origen') . '%';
        }

        if ($request->filled('grupo')) {
            $condiciones[] = "Grupo_Estiba.Nombre LIKE ?";
            $parametros[] = '%' . $request->input('grupo') . '%';
        }

        if ($request->filled('destino')) {
            $condiciones[] = "Nombre_Destino LIKE ?";
            $parametros[] = '%' . $request->input('destino') . '%';
        }

        if ($request->filled('fase')) {
            $condiciones[] = "Estado_Alistamiento = ?";
            $parametros[] = $request->input('fase');
        }

        if ($request->filled('est')) {
            $condiciones[] = "Remision.Estado LIKE ?";
            $parametros[] = '%' . $request->input('est') . '%';
        }

        if ($request->filled('fecha')) {
            $fechas = explode(' - ', $request->input('fecha'));
            $fecha_inicio = trim($fechas[0]);
            $fecha_fin = trim($fechas[1]);
            $condiciones[] = "Fecha BETWEEN ? AND ?";
            $parametros[] = $fecha_inicio;
            $parametros[] = $fecha_fin;
        }

        if ($request->filled('funcionario')) {
            $condiciones[] = "Identificacion_Funcionario = ?";
            $parametros[] = $request->input('funcionario');
        }

        if (empty($condiciones)) {
            $condiciones[] = "1 = 1";
        }

        $condicion = implode(' AND ', $condiciones);

        $remisiones = Remision::select('Remision.*', 'Grupo_Estiba.Nombre AS Grupo')
            ->leftJoin('Grupo_Estiba', 'Grupo_Estiba.Id_Grupo_Estiba', '=', 'Remision.Id_Grupo_Estiba')
            ->orderByDesc('Codigo')
            ->orderByDesc('Fecha')
            ->whereRaw($condicion, $parametros)
            ->paginate($request->input('pageSize', 10), ['*'], 'page', $request->input('page', 1));

        foreach ($remisiones as $remision) {
            $remision->Fase = ($remision->Estado_Alistamiento == 0) ? '1' : (($remision->Estado_Alistamiento == 1) ? '2' : 'Listo');
            $remision->Fecha_Remision = date('d/m/Y', strtotime($remision->Fecha));
            $remision->Items = $remision->productoRemisiones()->count();

            $remision->Punto_Origen = $remision->Nombre_Origen ?: '';
            $remision->Punto_Destino = $remision->Nombre_Destino ?: '';
        }

        return $this->success($remisiones);
    }


    public function detalleTipo(Request $request)
    {
        $tiposCantidad = Remision::selectRaw('Tipo, COUNT(*) as Cantidad')
            ->groupBy('Tipo')
            ->get();

        $anuladas = Remision::selectRaw('COUNT(Estado) as cantidad, Estado')
            ->where('Estado', 'Anulada')
            ->groupBy('Estado')
            ->first();

        $facturadas = Remision::whereNotNull('Id_Factura')
            ->where('Tipo', 'Cliente')
            ->count();

        $noFacturadas = Remision::whereNull('Id_Factura')
            ->where('Tipo', 'Cliente')
            ->count();

        $noconforme = NoConforme::where('Tipo', 'Remision')->count();

        $resultado = [
            'Tipo' => $tiposCantidad->toArray(),
            'Anuladas' => $anuladas ? $anuladas->toArray() : ['cantidad' => 0, 'Estado' => 'Anulada'],
            'Tipo_Facturacion' => ['Facturadas' => $facturadas, 'No_Facturadas' => $noFacturadas],
            'No_Conforme' => ['Cantidad' => $noconforme],
        ];

        return $this->success($resultado);
    }

    public function graficaRemisiones(Request $request)
    {
        // Obtener la fecha de hace 6 meses desde hoy
        $sixMonthsAgo = now()->subMonths(6)->format('Y-m-d');

        $graf2 = Remision::selectRaw('CONCAT(MONTHNAME(Fecha), "-", YEAR(Fecha)) as date')
            ->selectRaw('COUNT(Tipo) as Remisiones')
            ->whereBetween('Fecha', [$sixMonthsAgo, now()->format('Y-m-d')])
            ->groupByRaw('MONTH(Fecha), YEAR(Fecha)')
            ->orderBy('Fecha')
            ->get();

        return $this->success($graf2);
    }

    public function borradoresRemision(Request $request)
    {
        $query = Borrador::select('people.image', 'Borrador.Id_Borrador', 'Borrador.Tipo', 'Borrador.Fecha', 'Borrador.Id_Funcionario', 'Borrador.Nombre_Destino', 'Borrador.Codigo')
            ->join('people', 'Borrador.Id_Funcionario', '=', 'people.id')
            ->where('Borrador.Estado', 'Activo');

        if ($request->has('func') && $request->input('func') != "") {
            $query->where('Borrador.Id_Funcionario', $request->input('func'));
        }

        $query->orderBy('Borrador.Fecha', 'DESC');

        $resultados = $query->get();

        foreach ($resultados as $resultado) {
            $resultado->Fecha = Carbon::parse($resultado->Fecha)->format('Y-m-d H:i:s');
        }

        return $this->success($resultados);
    }

    public function descargaExcel(Request $request)
    {
        $id = (int) $request->input('id');

        return Excel::download(new DescargaExcelExport($id), 'Productos_Remision_' . $id . '.xlsx');
    }

    public function descargaZebra(Request $request)
    {
        $id = (isset($_REQUEST['id']) ? $_REQUEST['id'] : '');

        $oItem = new complex('Remision', "Id_Remision", $id);
        $rem = $oItem->getData();
        unset($oItem);


        if ($rem["Tipo_Destino"] == 'Bodega') {
            $rem["Tipo_Destino"] .= '_Nuevo';
        }

        if ($rem['Tipo_Destino'] == 'Cliente') {
            $oItem = new complex("third_parties", "id", $rem["Id_Destino"]);
        } else {
            $oItem = new complex($rem["Tipo_Destino"], "Id_" . $rem["Tipo_Destino"], $rem["Id_Destino"]);
        }

        $destino = $oItem->getData();
        unset($oItem);

        $nom = '';
        $tel = '';
        $mun = '';
        $dep = '';
        $direccion = '';
        $cod = '';

        if ($rem["Tipo_Destino"] == "Cliente") {
            $nom = $destino["social_reason"] ?? $destino['first_name'] . " N.I.T.:" . number_format($destino["nit"], 0, ",", ".");
            $tel = 'Tel. ' . $destino["landline"];

            $oItem = new complex("municipalities", "id", $destino["municipality_id"]);
            $mun = $oItem->getData();
            unset($oItem);

            $mun = $mun["name"];


            $oItem = new complex("departments", "id", $destino["department_id"]);
            $dep = $oItem->getData();
            unset($oItem);

            $dep = $dep["name"];

            $direccion = $destino["address_one"] . " " . $destino["address_two"] . " " . $destino["address_three"] . " " . $destino["address_four"] . " " . $destino["cod_dian_address"];

            $cod = $rem["Codigo"];
        } elseif ($rem["Tipo_Destino"] == "Bodega_Nuevo") {
            $nom = $destino["Nombre"] ?? $destino['Direccion'];
            $tel = 'Tel. ' . $destino["Telefono"];

            $mun = $destino["Direccion"];

            $dep = $destino["Mapa"];

            $direccion = $destino["Direccion"];
            $cod = $rem["Codigo"];
        } elseif ($rem["Tipo_Destino"] == "Contrato") {
            $oItemcliente = new complex("third_parties", "id", $destino["Id_Cliente"]);
            $cliente = $oItemcliente->getData();

            $nom = $cliente["social_reason"] ?? $cliente['first_name'] . " N.I.T.:" . number_format($cliente["nit"], 0, ",", ".");
            $tel = 'Tel. ' . $cliente["landline"];

            $oItem = new complex("municipalities", "id", $cliente["municipality_id"]);
            $mun = $oItem->getData();
            unset($oItem);

            $mun = $mun["name"];

            $oItem = new complex("departments", "id", $cliente["department_id"]);
            $dep = $oItem->getData();
            unset($oItem);

            $dep = $dep["name"];

            $direccion = $cliente["address_one"] . " " . $cliente["address_two"] . " " . $cliente["address_three"] . " " . $cliente["address_four"] . " " . $cliente["cod_dian_address"];

            $cod = $rem["Codigo"];
        } elseif ($rem["Tipo_Destino"] == "Punto_Dispensacion") {
            $nom = $destino["Nombre"] ?? $destino['Direccion'];
            $tel = 'Tel. ' . $destino["Telefono"];
            $mun = $destino["Municipio"];
            $dep = $destino["Departamento"];
            $direccion = $destino["Direccion"];
            $cod = $rem["Codigo"];
        } else {
            // Si ninguno de los tipos de destino coincide, asignar valores predeterminados o realizar acciones adicionales según sea necesario.
            $nom = $destino["Nombre"] ?? '';
            $tel = 'Tel. ' . $destino["Telefono"] ?? '';

            $oItem = new complex("departments", "id", $destino["Nombre"]);
            $dep = $oItem->getData();
            unset($oItem);

            $oItem = new complex("municipalities", "id", $destino["Ciudad"]);
            $mun = $oItem->getData();
            unset($oItem);
        }

        $header = (object) [
            'Titulo' => 'Zebra',
            'Codigo' => $rem["code"] ?? '',
            'Fecha' => $rem["created_at"] ?? '',
            'CodigoFormato' => $rem["format_code"] ?? '',
        ];

        $pdf = Pdf::loadView('pdf.zebra', [
            'data' => $rem,
            'datosCabecera' => $header,
            'nom' => $nom,
            'tel' => $tel,
            'mun' => $mun,
            'dep' => $dep,
            'direccion' => $direccion,
            'cod' => $cod,
        ]);

        return $pdf->stream("Zebra");
    }

    public function descargaPdfPrice(Request $request)
    {

        $tipo = (isset($_REQUEST['tipo']) ? $_REQUEST['tipo'] : '');
        $id = (isset($_REQUEST['id']) ? $_REQUEST['id'] : '');

        /* DATOS DEL ARCHIVO A MOSTRAR */
        $oItem = new complex($tipo, "Id_" . $tipo, $id);
        $data = $oItem->getData();
        unset($oItem);
        /* FIN DATOS DEL ARCHIVO A MOSTRAR */


        switch ($tipo) {
            case 'Remision': {
                $query = 'SELECT PR.Lote, PR.Fecha_Vencimiento, IFNULL(CONCAT( P.Principio_Activo, " ",
                P.Presentacion, " ",
                P.Concentracion, " (", P.Nombre_Comercial,") ",
                P.Cantidad," ",
                P.Unidad_Medida, " " ), CONCAT(P.Nombre_Comercial," LAB-", P.Laboratorio_Comercial)) AS Nombre_Producto, PR.Cantidad, PR.Precio, PR.Descuento, PR.Impuesto, PR.Subtotal, P.Laboratorio_Generico, P.Embalaje
                FROM Producto_Remision PR
                INNER JOIN Producto P ON PR.Id_Producto=P.Id_Producto
                WHERE PR.Id_Remision=' . $id . ' ORDER BY Nombre_Producto';

                $oCon = new consulta();
                $oCon->setQuery($query);
                $oCon->setTipo('Multiple');
                $productos = $oCon->getData();
                unset($oCon);
                $productos = json_decode(json_encode($productos), true);


                if ($data["Tipo_Origen"] == 'Bodega') {
                    $data["Tipo_Origen"] .= '_Nuevo';
                }
                if ($data["Tipo_Destino"] == 'Bodega') {
                    $data["Tipo_Destino"] .= '_Nuevo';
                }

                $oItem = new complex($data["Tipo_Origen"], "Id_" . $data["Tipo_Origen"], $data["Id_Origen"]);
                $origen = $oItem->getData();
                unset($oItem);

                $oItem = new complex($data["Tipo_Destino"], "Id_" . $data["Tipo_Destino"], $data["Id_Destino"]);
                $destino = $oItem->getData();
                unset($oItem);


                $oItem = new complex('people', "identifier", $data["Identificacion_Funcionario"]);
                $elabora = $oItem->getData();
                unset($oItem);

            }


                $fecha = $data['Fecha'];

                $header = (object) [
                    'Titulo' => 'Precio Productos',
                    'Codigo' => $data["code"] ?? '',
                    'Fecha' => $fecha,
                    'CodigoFormato' => $data["format_code"] ?? '',
                ];

                $pdf = Pdf::loadView('pdf.pdf_price', [
                    'data' => $data,
                    'datosCabecera' => $header,
                    'origen' => $origen,
                    'destino' => $destino,
                    'elabora' => $elabora,
                    'productos' => $productos
                ]);

                return $pdf->stream("pdf_price");

        }

    }

    public function descargaPdf(Request $request)
    {

        $tipo = (isset($_REQUEST['tipo']) ? $_REQUEST['tipo'] : '');
        $id = (isset($_REQUEST['id']) ? $_REQUEST['id'] : '');




        /* DATOS DEL ARCHIVO A MOSTRAR */
        $oItem = new complex($tipo, "Id_" . $tipo, $id);
        $data = $oItem->getData();
        unset($oItem);

        $query = "SELECT *, (SELECT CONCAT(F.first_name,' ',F.first_surname) FROM people F WHERE F.identifier=R.Fase_1) as Fase1, (SELECT CONCAT(F.first_name,' ',F.first_surname) FROM people F WHERE F.identifier=R.Fase_2) as Fase2,(SELECT signature_blob FROM people WHERE identifier=R.Fase_1) as Firma1, (SELECT signature_blob FROM people WHERE identifier=R.Fase_2) as Firma2 FROM Remision R WHERE R.Id_Remision=$id";

        $oCon = new consulta();
        $oCon->setQuery($query);
        $data = $oCon->getData();
        unset($oCon);
        /* FIN DATOS DEL ARCHIVO A MOSTRAR */

        ob_start(); // Se Inicializa el gestor de PDF



        /* HAGO UN SWITCH PARA TODOS LOS MODULOS QUE PUEDEN GENERAR PDF */
        switch ($tipo) {
            case 'Remision': {
                $query = "SELECT
        PR.Lote,
        PR.Fecha_Vencimiento,
        IFNULL(CONCAT(P.Principio_Activo, ' ', P.Presentacion, ' ', P.Concentracion, ' (', P.Nombre_Comercial, ') ', P.Cantidad, ' ', P.Unidad_Medida,                        ' '),
                CONCAT(P.Nombre_Comercial, ' LAB-', P.Laboratorio_Comercial)) AS Nombre_Producto,
        PR.Cantidad,
        PR.Precio,
        PR.Descuento,
        PR.Impuesto,
        PR.Subtotal,
        P.Laboratorio_Generico,
        P.Embalaje,
        I.Grupo
        FROM
            Producto_Remision PR
        INNER JOIN Producto P ON PR.Id_Producto = P.Id_Producto
        LEFT JOIN
            (SELECT G.Nombre as Grupo, I.Id_Inventario_Nuevo
                FROM  Inventario_Nuevo I
                INNER JOIN Estiba E ON E.Id_Estiba = I.Id_Estiba
                INNER JOIN Grupo_Estiba G ON G.Id_Grupo_Estiba = E.Id_Grupo_Estiba
            ) I ON I.Id_Inventario_Nuevo = PR.Id_Inventario_Nuevo
        WHERE
            PR.Id_Remision = $id
        ORDER BY Nombre_Producto";

                $oCon = new consulta();
                $oCon->setQuery($query);
                $oCon->setTipo('Multiple');
                $productos = $oCon->getData();
                unset($oCon);
                $productos = json_decode(json_encode($productos), true);

                // header("Content-type:application/json");

                // echo json_encode($productos); exit;

                $productos_ = array_filter($productos, function ($k, $v) {
                    return $k['Grupo'] !== 'NEVERA';
                }, ARRAY_FILTER_USE_BOTH);
                $productos_nev = array_filter($productos, function ($k, $v) {
                    return $k['Grupo'] == 'NEVERA';
                }, ARRAY_FILTER_USE_BOTH);
                $productos = array_values($productos_);
                $productos_nev = array_values($productos_nev);

                if ($data["Tipo_Origen"] == 'Bodega') {
                    $data["Tipo_Origen"] .= '_Nuevo';
                }
                if ($data["Tipo_Destino"] == 'Bodega') {
                    $data["Tipo_Destino"] .= '_Nuevo';
                }

                if ($data["Tipo_Origen"] == 'Cliente') {
                    $data["Tipo_Origen"] = 'third_parties';
                    $oItem = new complex($data["Tipo_Origen"], 'id', $data["Id_Origen"]);
                    $origen = $oItem->getData();
                    unset($oItem);
                } else {
                    $oItem = new complex($data["Tipo_Origen"], "Id_" . $data["Tipo_Origen"], $data["Id_Origen"]);
                    $origen = $oItem->getData();
                    unset($oItem);
                }
                if ($data["Tipo_Destino"] == 'Cliente') {
                    $data["Tipo_Destino"] = 'third_parties';
                    $oItem = new complex($data["Tipo_Destino"], 'id', $data["Id_Destino"]);
                    $destino = $oItem->getData();
                    unset($oItem);
                } else {
                    $oItem = new complex($data["Tipo_Destino"], "Id_" . $data["Tipo_Destino"], $data["Id_Destino"]);
                    $destino = $oItem->getData();
                    unset($oItem);
                }


                $oItem = new complex('people', "identifier", $data["Identificacion_Funcionario"]);
                $elabora = $oItem->getData();
                unset($oItem);


                $fecha = $data['Fecha'];

                $header = (object) [
                    'Titulo' => 'Detalle de Envío',
                    'Codigo' => $data->code ?? '',
                    'Fecha' => $fecha,
                    'CodigoFormato' => $data->format_code ?? '',
                ];

                $pdf = Pdf::loadView('pdf.descarga_pdf_remision', [
                    'data' => $data,
                    'datosCabecera' => $header,
                    'origen' => $origen,
                    'destino' => $destino,
                    'elabora' => $elabora,
                    'productos' => $productos
                ]);

                return $pdf->stream("descarga_pdf_remision");
            }
        }
    }

    public function cupoCliente()
    {
        $id_destino = (isset($_REQUEST['id_destino']) ? $_REQUEST['id_destino'] : '');

        $query = "SELECT
            C.assigned_space as cupo,
            R.Id_Cliente,
            R.Nombre,
            MAX(R.Dias_Mora) AS Dias_Mora,
            SUM(R.TOTAL) AS CupoUsado,
            IFNULL(RE.Costo_Remision, 0) AS Cupo_Remisiones,
            RE.Cods
            FROM
            (SELECT
                  MC.Id_PLan_Cuenta,
                        C.id as Id_Cliente,
                        IFNULL(C.social_reason, CONCAT_WS(' ', C.first_name, C.first_surname)) as Nombre,
                        MC.Fecha_Movimiento,
                        IF(C.condition_payment > 1, IF(DATEDIFF(CURDATE(), DATE(MC.Fecha_Movimiento)) > C.condition_payment, DATEDIFF(CURDATE(), DATE(MC.Fecha_Movimiento)) - C.condition_payment, 0), 0) AS Dias_Mora,
                        (CASE PC.Naturaleza
                        WHEN 'C' THEN (SUM(MC.Haber) - SUM(MC.Debe))
                        ELSE (SUM(MC.Debe) - SUM(MC.Haber))
                        END) AS TOTAL
            FROM
                  Movimiento_Contable MC
            INNER JOIN Plan_Cuentas PC ON MC.Id_Plan_Cuenta = PC.Id_Plan_Cuentas
            INNER JOIN third_parties C ON C.id = MC.Nit
            WHERE
                  MC.Estado != 'Anulado'
                        AND C.id = $id_destino
                        AND Id_Plan_Cuenta = 57
            GROUP BY MC.Documento , C.id , MC.Id_Plan_Cuenta
            HAVING TOTAL != 0) R
                  INNER JOIN
            third_parties C ON C.id = R.Id_Cliente
                  LEFT JOIN
            (SELECT
                  RE.Id_Destino,
                        SUM(RE.Subtotal_Remision) AS Costo_Remision,
                        GROUP_CONCAT(RE.Codigo) AS Cods
            FROM
                  Remision RE
            WHERE
                  RE.Estado NOT IN ('Facturada' , 'Anulada')
                        AND RE.Tipo_Destino = 'Cliente'
            GROUP BY RE.Id_Destino) RE ON RE.Id_Destino = C.id
            WHERE
            C.assigned_space > 0";

        $oCon = new consulta();
        $oCon->setQuery($query);
        $cupo = $oCon->getData();
        return $this->success($cupo);
    }

    public function remision()
    {

        $id = (isset($_REQUEST['id']) ? $_REQUEST['id'] : '');

        $query = 'SELECT R.*
        FROM Remision R
        WHERE R.Id_Remision=' . $id;
        $oCon = new consulta();
        //$oCon->setTipo('Multiple');
        $oCon->setQuery($query);
        $remision = $oCon->getData();
        // return $remision;
        unset($oCon);
        $bodega = '';

        if ($remision['Tipo_Origen'] == 'Bodega') {
            # code...
            $remision['Tipo_Origen'] .= '_Nuevo';

        }
        if ($remision['Tipo_Destino'] == 'Bodega') {
            # code...
            $remision['Tipo_Destino'] .= '_Nuevo';

        }

        switch ($remision['Tipo_Origen']) {
            case 'Bodega_Nuevo':
                $query = 'SELECT *
                FROM ' . $remision['Tipo_Origen'] . $bodega . '
                WHERE Id_' . $remision['Tipo_Origen'] . '=' . $remision['Id_Origen'];
                $oCon = new consulta();
                //$oCon->setTipo('Multiple');
                $oCon->setQuery($query);


                $origen = $oCon->getData();
                unset($oCon);
                # code...
                break;
            case 'Cliente':
                $query = 'SELECT  *, dian_address as Direccion, cell_phone as Telefono IFNULL(third_parties.social_reason, CONCAT_WS(" ", third_parties.first_name, third_parties.first_surname)) AS Nombre
                FROM third_parties
                WHERE id =' . $remision['Id_Origen'];
                $oCon = new consulta();
                $oCon->setQuery($query);

                $origen = $oCon->getData();
                unset($oCon);

            default:
                # code...
                break;
        }



        if ($remision['Tipo_Destino'] == 'Contrato') {
            $query = 'SELECT * , Nombre_Contrato AS Nombre
            FROM ' . $remision['Tipo_Destino'] . '
            WHERE Id_' . $remision['Tipo_Destino'] . '=' . $remision['Id_Destino'];
        }

        if ($remision['Tipo_Destino'] == 'Cliente') {
            $query = 'SELECT *, dian_address as Direccion, cell_phone as Telefono, IFNULL(third_parties.social_reason, CONCAT_WS(" ", third_parties.first_name, third_parties.first_surname)) AS Nombre
            FROM third_parties
            WHERE id=' . $remision['Id_Destino'];
        }

        if (!in_array($remision['Tipo_Destino'], ['Cliente', 'Contrato'])) {
            $query = 'SELECT *
            FROM ' . $remision['Tipo_Destino'] . '
            WHERE Id_' . $remision['Tipo_Destino'] . '=' . $remision['Id_Destino'];
        }


        $oCon = new consulta();
        //$oCon->setTipo('Multiple');
        $oCon->setQuery($query);
        $destino = $oCon->getData();
        unset($oCon);


        if ($remision['Tipo_Lista'] == "Contrato") {
            $oItem = new complex('Contrato', 'Id_Contrato', $remision['Id_Lista']);
            $contrato = $oItem->getData();
            $resultado['Contrato'] = $contrato;
            unset($oItem);
        } elseif ($remision['Tipo_Lista'] == "Lista_Ganancia") {
            $oItem = new complex('winning_lists', 'id', $remision['Id_Lista']);
            $contrato = $oItem->getData();
            $resultado['Lista'] = $contrato;
            unset($oItem);
        }

        $resultado['Remision'] = $remision;
        $resultado['Origen'] = $origen;
        $resultado['Destino'] = $destino;

        return $this->success($resultado);

    }

    public function productosRemision()
    {
        $idremision = (isset($_REQUEST['id']) ? $_REQUEST['id'] : '');

        $query = "SELECT PR.Lote, PR.Fecha_Vencimiento, P.Id_Categoria,
        IFNULL(CONCAT( P.Principio_Activo, ' ',P.Presentacion, ' ',P.Concentracion, ' (', P.Nombre_Comercial,') ',P.Cantidad,' ',P.Unidad_Medida, ' LAB-', P.Laboratorio_Comercial ), CONCAT(P.Nombre_Comercial,' LAB-', P.Laboratorio_Comercial)) AS Nombre_Producto,
        PR.Cantidad,
        PR.Precio, PR.Descuento, PR.Impuesto, PR.Subtotal,
        I.Nombre AS Grupo
        FROM Producto_Remision PR
        INNER JOIN Producto P ON PR.Id_Producto=P.Id_Producto
        LEFT JOIN (SELECT G.Nombre, I.Id_Inventario_Nuevo
        FROM Inventario_Nuevo I
        INNER JOIN Estiba E ON E.Id_Estiba=I.Id_Estiba
        INNER JOIN Grupo_Estiba G ON G.Id_Grupo_Estiba = E.Id_Grupo_Estiba
        ) I on I.Id_Inventario_Nuevo = PR.Id_Inventario_Nuevo
        WHERE PR.Id_Remision='$idremision' ORDER BY Nombre_Producto";

        // echo $query; exit;
        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->setTipo('Multiple');
        $productos = $oCon->getData();
        unset($oCon);
        return $this->success($productos);
    }

    public function actividadesRemision()
    {
        $id = (isset($_REQUEST['id']) ? $_REQUEST['id'] : '');

        $query = "SELECT AR.*, F.image as Imagen, F.full_name as Funcionario,
        (CASE
            WHEN AR.Estado='Creacion' THEN CONCAT('1 ',AR.Estado)
            WHEN AR.Estado='Alistamiento' THEN CONCAT('2 ',AR.Estado)
            WHEN AR.Estado='Edicion' THEN CONCAT('2 ',AR.Estado)
            WHEN AR.Estado='Fase 1' THEN CONCAT('2 ',AR.Estado)
            WHEN AR.Estado='Fase 2' THEN CONCAT('3 ',AR.Estado)
            WHEN AR.Estado='Enviada' THEN CONCAT('4 ',AR.Estado)
            WHEN AR.Estado='Facturada' THEN CONCAT('5 ',AR.Estado)
            WHEN AR.Estado='Recibida' THEN CONCAT('5 ',AR.Estado)
            WHEN AR.Estado='Anulada' THEN CONCAT('6 ',AR.Estado)
        END) as Estado2, (CASE
            WHEN AR.Estado='Anulada' THEN CONCAT(' ', AR.Detalles,'. Con la suguiente Observacion: ', IFNULL(R.Observacion_Anulacion, ''))
            WHEN AR.Estado!='Anulada' THEN AR.Detalles
        END ) as Detalles
        FROM Actividad_Remision AR
        INNER JOIN people F On AR.Identificacion_Funcionario=F.id
        INNER JOIN Remision R ON AR.Id_Remision=R.Id_Remision
        WHERE AR.Id_Remision=$id
        UNION ALL(
            SELECT ANC.*, F.image as Imagen, F.full_name as Funcionario,
            '' as Estado2, ANC.Detalles
            From Actividad_No_Conforme_Remision ANC
            INNER JOIN people F On ANC.Identificacion_Funcionario=F.id
            Inner Join No_Conforme NC on NC.Id_No_Conforme = ANC.Id_No_Conforme
            INNER JOIN Remision R ON NC.Id_Remision=R.Id_Remision
            WHERE R.Id_Remision=$id
        )



        Order BY Fecha ASC, Id_Actividad_Remision ASC";

        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->setTipo('Multiple');
        $actividades = $oCon->getData();
        unset($oCon);


        return $this->success($actividades);
    }

    public function getProductosInventario()
    {
        $http_response = new HttpResponse();
        $queryObj = new QueryBaseDatos();
        $util = new Utility();

        $tipo = (isset($_REQUEST['tiporemision']) ? $_REQUEST['tiporemision'] : '');
        $cliente = (isset($_REQUEST['id_destino']) ? $_REQUEST['id_destino'] : '');
        $id_categoria = (isset($_REQUEST['id_categoria']) ? $_REQUEST['id_categoria'] : '');
        $mes = isset($_REQUEST['mes']) ? $_REQUEST['mes'] : '';
        $tipo_bodega = '';

        $tipo_bodega = isset($_REQUEST['modelo']) ? $_REQUEST['modelo'] : '';
        $id_origen = isset($_REQUEST['id_origen']) ? $_REQUEST['id_origen'] : '';
        $id_grupo = isset($_REQUEST['id_grupo']) ? $_REQUEST['id_grupo'] : '';
        $grupo = isset($_REQUEST['grupo']) ? $_REQUEST['grupo'] : '';


        $grupo = (array) json_decode($grupo, true);

        $tipo_bodega = explode('-', $tipo_bodega); // origen y desitino

        if ($mes > '0') {
            $hoy = date("Y-m-t", strtotime(date('Y-m-d')));
            $nuevafecha = strtotime('+' . $mes . ' months', strtotime($hoy));
            $nuevafecha = date('Y-m-d', $nuevafecha);
        } else {
            $nuevafecha = date('Y-m-d');
        }
        $condicion_principal = '';
        [$condicion, $condicion_principal] = $this->SetCondiciones($_REQUEST, $nuevafecha, $condicion_principal, $tipo_bodega, $id_origen, $id_grupo, $mes, $grupo, $id_categoria);
        $query = $this->GetQuery($tipo, $condicion, $condicion_principal, $cliente, $queryObj, $tipo_bodega);
        $queryObj->SetQuery($query);
        $productos = $queryObj->ExecuteQuery('Multiple');


        $productos = $this->GetLotes($productos, $queryObj, $condicion_principal, $tipo, $tipo_bodega);

        $productosIds = array_column($productos, 'Id_Producto');

        [$productosConVariables, $variablesLabels] = $this->obtenerProductosConVariables($productosIds);

        $productosConVariablesMap = [];

        foreach ($productosConVariables as $producto) {
            $productosConVariablesMap[$producto->Id_Producto] = $producto->variables;
        }

        foreach ($productos as $producto) {
            $producto->variables = $productosConVariablesMap[$producto->Id_Producto] ?? [];
        }

        return $this->success([
            'productos' => $productos,
            'variables' => $variablesLabels,
        ]);
    }

    private function SetCondiciones($req, $nuevafecha, $condicion_principal, $tipo_bodega, $id_origen, $id_grupo, $mes, $grupo, $id_categoria)
    {


        $condicion = '';

        if ($tipo_bodega[0] == 'Bodega') {
            $condicion_grupo = '';

            $condicion_principal .= '
                INNER JOIN Bodega_Nuevo B ON I.Id_Bodega = B.Id_Bodega_Nuevo
                WHERE B.Id_Bodega_Nuevo = ' . $id_origen . $condicion_grupo;

            if ($tipo_bodega[1] != 'Bodega' && ($mes != '-1')) {
                /* if ($tipo_bodega[1] != 'Bodega' && ($grupo['Fecha_Vencimiento'] == 'Si' && $mes != '-1')) {

                    $condicion_principal .= "  AND I.Fecha_Vencimiento>='$nuevafecha' ";
                } */

            } else if ($tipo_bodega[0] == 'Punto') {
                $condicion_principal = "
            INNER JOIN Punto_Dispensacion B ON I.Id_Punto_Dispensacion = B.Id_Punto_Dispensacion
            WHERE B.Id_Punto_Dispensacion=$req[id_origen] ";
            }


            if (isset($req['nombre']) && $req['nombre']) {
                $condicion .= " AND (PRD.Nombre_Comercial LIKE '%$req[nombre]%')";
            }

            if (isset($req['cod_barra']) && $req['cod_barra'] != '') {
                $condicion .= " AND PRD.Codigo_Barras LIKE '%" . $req['cod_barra'] . "%'";
            }
            if (isset($id_categoria) && $id_categoria != '') {
                $condicion .= " AND CAT.Id_Categoria_Nueva = $id_categoria";
            }

            return [$condicion, $condicion_principal];
        }
    }

    private function GetQuery($tipo, $condicion, $condicion_principal, $cliente, $queryObj, $tipo_bodega)
    {
        $having = " GROUP BY I.Id_Producto HAVING Cantidad_Disponible>0 ORDER BY Nombre_Comercial";
        $id_origen = $_REQUEST['id_origen'];
        // $subquery_bodega = $tipo_bodega[0] == 'Bodega' ? "(SELECT Aplica_Separacion_Categorias FROM Bodega WHERE Id_Bodega = $id_origen) AS Aplica_Separacion_Categorias," : "";
        $subquery_bodega = "";

        $dynamic_filter_subquery = "";
        if (isset($_REQUEST['dinamic'])) {
            $dynamic_filters = is_string($_REQUEST['dinamic']) ? json_decode($_REQUEST['dinamic'], true) : $_REQUEST['dinamic'];

            if (is_array($dynamic_filters)) {
                $conditions = [];
                $category_filters = [];
                foreach ($dynamic_filters as $filter) {
                    if (isset($filter['category_variable_id']) && isset($filter['value']) && !empty($filter['value'])) {
                        $category_variable_id = $filter['category_variable_id'];
                        $value = $filter['value'];
                        $conditions[] = "(VP.category_variables_id = $category_variable_id AND VP.valor LIKE '%$value%')";
                        $category_filters[] = $category_variable_id; // Agregar los IDs de las categorías filtradas
                    }
                }
                // Verificar si se han aplicado filtros
                if (!empty($conditions)) {
                    // Crear una subconsulta dinámica para filtrar los productos
                    $dynamic_filter_subquery = " AND PRD.Id_Producto IN (
                        SELECT VP.product_id
                        FROM variable_products VP
                        WHERE " . implode(" OR ", $conditions) . "
                        GROUP BY VP.product_id
                        HAVING COUNT(DISTINCT VP.category_variables_id) = " . count(array_unique($category_filters)) . "
                    )";
                }
            }
        }

        if ($tipo == 'Interna') {
            $query = 'SELECT SubC.Nombre AS Subcategoria, SubC.Separable AS Categoria_Separable, PRD.Id_Subcategoria,  PRD.Id_Producto,
             IFNULL((SELECT Costo_Promedio  FROM Costo_Promedio WHERE Id_Producto = PRD.Id_Producto),"0") as  Precio,
             SUM(I.Cantidad - (I.Cantidad_Apartada+I.Cantidad_Seleccionada)) as Cantidad_Disponible,
             PRD.Nombre_Comercial,PRD.Id_Producto, 0 as Seleccionado, NULL as Cantidad, ' . $subquery_bodega . ' (
                    CASE
                    WHEN PRD.Gravado="Si" THEN (SELECT Valor FROM Impuesto WHERE Valor>0 ORDER BY Id_Impuesto DESC LIMIT 1)
                    WHEN PRD.Gravado="No"  THEN 0
                  END
                ) as Impuesto, IFNULL((SELECT Costo_Promedio  FROM Costo_Promedio WHERE Id_Producto = PRD.Id_Producto),"0") as Costo
                FROM Inventario_Nuevo I
                INNER JOIN Producto PRD
                On I.Id_Producto=PRD.Id_Producto
                INNER JOIN Categoria_Nueva CAT
                On CAT.Id_Categoria_Nueva = PRD.Id_Categoria
                INNER JOIN Subcategoria SubC ON PRD.Id_Subcategoria = SubC.Id_Subcategoria ' . $condicion_principal . $dynamic_filter_subquery . $condicion . $having;

        } else {
            $query1 = "SELECT * FROM third_parties WHERE is_client='1' and id=" . $cliente;
            $queryObj->SetQuery($query1);
            $datoscliente = $queryObj->ExecuteQuery('simple');
            if ($datoscliente['winning_list_id'] == null) {
                $datoscliente['winning_list_id'] = 0;
            }

            $query = ' SELECT T.*,
                        ( CASE WHEN PRG.Precio IS NOT NULL THEN PRG.Precio WHEN PRG.Precio IS  NULL THEN 0 END ) as Precio_Regulado
                    FROM
                        (SELECT
                            SubC.Nombre as Subcategoria,
                            SubC.Separable AS Categoria_Separable,
                            PRD.Id_Subcategoria,  PRD.Id_Producto,
                            IFNULL((IF (IFNULL((SELECT Precio FROM Precio_Regulado WHERE Id_Producto=PRD.Id_Producto ORDER BY Precio desc LIMIT 1),0) <  LG.Precio
                            AND  IFNULL((SELECT Precio FROM Precio_Regulado WHERE Id_Producto=PRD.Id_Producto ORDER BY Precio desc LIMIT 1),0)>0 , IFNULL((SELECT ROUND( Precio,2) FROM Precio_Regulado WHERE Id_Producto=PRD.Id_Producto ORDER BY Precio desc LIMIT 1),0),LG.Precio   )),0) as Precio,
                        0 as Seleccionado, NULL as Cantidad,
                        SUM(I.Cantidad-(I.Cantidad_Apartada+I.Cantidad_Seleccionada)) as Cantidad_Disponible,
                        PRD.Nombre_Comercial,
                        (
                            CASE
                            WHEN PRD.Gravado="Si" THEN (SELECT Valor FROM Impuesto WHERE Valor>0 ORDER BY Id_Impuesto DESC LIMIT 1)
                            WHEN PRD.Gravado="No"  THEN 0
                            END
                        ) as Impuesto,
                        IFNULL((SELECT Costo_Promedio  FROM Costo_Promedio WHERE Id_Producto = PRD.Id_Producto),"0") as Costo
                        FROM Inventario_Nuevo I
                        INNER JOIN Producto PRD On I.Id_Producto=PRD.Id_Producto
                        INNER JOIN Categoria_Nueva CAT
                        On CAT.Id_Categoria_Nueva = PRD.Id_Categoria
                        INNER JOIN Subcategoria SubC ON PRD.Id_Subcategoria = SubC.Id_Subcategoria
                        INNER JOIN Producto_Lista_Ganancia LG ON PRD.Id_Producto = LG.Id_Producto ' . $condicion_principal . $dynamic_filter_subquery . $condicion . ' AND LG.Id_Lista_Ganancia =' . $datoscliente['winning_list_id'] . $having . '  )
                        T left JOIN (SELECT Precio, Id_producto FROM Precio_Regulado) PRG ON T.Id_Producto=PRG.Id_Producto ';
        }
        // echo $query;
        // exit;
        return $query;
    }

    private function GetLotes($productos, $queryObj, $condicion_principal, $tipo, $tipo_bodega)
    {


        $condicionBodega = ' ';
        if ($tipo_bodega[0] == 'Bodega') {
            $condicionBodega .= '
            INNER JOIN Producto PRD
            On I.Id_Producto=PRD.Id_Producto
            LEFT JOIN Subcategoria SubC ON PRD.Id_Subcategoria = SubC.Id_Subcategoria ';
        }


        $resultado = [];
        $having = "  HAVING Cantidad>0 ORDER BY I.Fecha_Vencimiento ASC";
        $i = -1;
        $pos = 0;
        foreach ($productos as $value) {
            $i++;
            if ($tipo == 'Cliente') {
                $productos[$i]->Costo = $this->GetCosto($value->Id_Producto);
            }
            $query1 = "SELECT I.Id_Inventario_Nuevo,
                              I.Id_Producto,I.Lote,(I.Cantidad-(I.Cantidad_Apartada+I.Cantidad_Seleccionada)) as Cantidad,
                              I.Fecha_Vencimiento,$value->Precio as Precio, 0 as Cantidad_Seleccionada FROM Inventario_Nuevo I ";

            $query1 .= $condicionBodega . $condicion_principal . " AND I.Id_Producto= $value->Id_Producto " . $having;

            $queryObj->SetQuery($query1);
            $lotes = $queryObj->ExecuteQuery('Multiple');

            if (count($lotes) > 0) {
                $resultado[$pos] = $productos[$i];
                $resultado[$pos]->Lotes = $lotes;
                $pos++;
            } else {
                unset($productos[$i]);
            }
        }

        return $resultado;
    }

    private function GetCosto($idProducto)
    {
        $oCon = new consulta();
        $query = 'SELECT Costo_Promedio FROM Costo_Promedio WHERE Id_Producto = ' . $idProducto;
        $oCon->setQuery($query);
        $costo = $oCon->getData();
        unset($oCon);
        return $costo['Costo_Promedio'];
    }

    public function getProductosInventarioPost()
    {
        $http_response = new HttpResponse();
        $queryObj = new QueryBaseDatos();
        $util = new Utility();

        $tipo = (isset($_REQUEST['tiporemision']) ? $_REQUEST['tiporemision'] : '');
        $cliente = (isset($_REQUEST['id_destino']) ? $_REQUEST['id_destino'] : '');
        $mes = isset($_REQUEST['mes']) ? $_REQUEST['mes'] : '';
        $tipo_bodega = '';

        if ($mes > '0') {
            $hoy = date("Y-m-t", strtotime(date('Y-m-d')));
            $nuevafecha = strtotime('+' . $mes . ' months', strtotime($hoy));
            $nuevafecha = date('Y-m-d', $nuevafecha);

        } else {
            $nuevafecha = date('Y-m-d');
        }
        $condicion_principal = '';
        $condicion = $this->SetCondicionesPost($_REQUEST, $nuevafecha, $condicion_principal, $tipo_bodega);

        $query = $this->GetQueryPost($tipo, $condicion, $condicion_principal, $cliente, $queryObj, $tipo_bodega);
        $queryObj->SetQuery($query);
        $productos = $queryObj->ExecuteQuery('Multiple');



        $productos = $this->GetLotesPost($productos, $queryObj, $condicion_principal, $tipo);

        return $this->success($productos);
    }

    function SetCondicionesPost($req, $nuevafecha, $condicion_principal, $tipo_bodega)
    {
        $tipo_bodega = isset($_REQUEST['modelo']) ? $_REQUEST['modelo'] : '';
        $bodega = isset($_REQUEST['id_origen']) ? $_REQUEST['id_origen'] : '';

        $tipo_bodega = explode('-', $tipo_bodega);


        $condicion = '';

        if ($tipo_bodega[0] == 'Bodega') {
            $criterio_categorias = "SELECT Id_Categoria FROM Bodega_Categoria WHERE Id_Bodega = $req[id_origen]";
            $condicion .= " AND PRD.Id_Categoria IN ($criterio_categorias) ";
            if (($tipo_bodega[1] == 'Bodega' || ($bodega == '6' || $bodega == '8' || $bodega == '9'))) {
                $condicion_principal = " WHERE I.Id_Bodega=$req[id_origen]";
            } else {
                $condicion_principal = " WHERE I.Id_Bodega=$req[id_origen] AND I.Fecha_Vencimiento>='$nuevafecha'";
            }


        } else if ($tipo_bodega[0] == 'Punto') {
            $condicion_principal = " WHERE Id_Punto_Dispensacion=$req[id_origen] ";
        }


        if (isset($req['nombre']) && $req['nombre']) {
            $condicion .= " AND (PRD.Nombre_Comercial LIKE '%$req[nombre]%' OR  CONCAT(PRD.Principio_Activo,' ',PRD.Presentacion,' ',PRD.Concentracion,' ', PRD.Cantidad,' ', PRD.Unidad_Medida) LIKE '%$req[nombre]%' )";
        }

        if (isset($req['cum']) && $req['cum']) {
            $condicion .= " AND PRD.Codigo_Cum LIKE '%" . $req['cum'] . "%'";
        }

        if (isset($req['lab_com']) && $req['lab_com']) {
            $condicion .= " AND PRD.Laboratorio_Comercial LIKE '%" . $req['lab_com'] . "%'";
        }

        if (isset($req['cod_barra']) && $req['cod_barra'] != '') {
            $condicion .= " AND PRD.Codigo_Barras LIKE '%" . $req['cod_barra'] . "%'";
        }

        return $condicion;
    }

    function GetQueryPost($tipo, $condicion, $condicion_principal, $cliente, $queryObj, $tipo_bodega)
    {
        $having = " GROUP BY I.Id_Producto HAVING Cantidad_Disponible>0 ORDER BY Nombre_Comercial";
        $id_origen = $_REQUEST['id_origen'];
        $subquery_bodega = "";
        if ($tipo == 'Interna') {
            $query = 'SELECT C.Nombre AS Categoria, C.Separable AS Categoria_Separable, PRD.Id_Categoria,  PRD.Id_Producto,ROUND(IFNULL(AVG(I.Costo),0)) as Precio, PRD.Embalaje,SUM(I.Cantidad-(I.Cantidad_Apartada+I.Cantidad_Seleccionada)) as Cantidad_Disponible, CONCAT(PRD.Principio_Activo," ",PRD.Presentacion," ",PRD.Concentracion," ", PRD.Cantidad," ", PRD.Unidad_Medida) as Nombre,  PRD.Nombre_Comercial, PRD.Laboratorio_Comercial,PRD.Laboratorio_Generico,PRD.Codigo_Cum, PRD.Cantidad_Presentacion, 0 as Seleccionado, NULL as Cantidad, ' . $subquery_bodega . ' (
                CASE
                WHEN PRD.Gravado="Si" THEN (SELECT Valor FROM Impuesto WHERE Valor>0 ORDER BY Id_Impuesto DESC LIMIT 1)
                WHEN PRD.Gravado="No"  THEN 0
              END
            ) as Impuesto, (SELECT ROUND(AVG(Costo)) FROM Inventario_Nuevo WHERE Id_Bodega!=0 AND Id_Producto=I.Id_Producto) as Costo
            FROM Inventario_Nuevo I
            INNER JOIN Producto PRD
            On I.Id_Producto=PRD.Id_Producto
            INNER JOIN Categoria C ON PRD.Id_Categoria = C.Id_Categoria ' . $condicion_principal . $condicion . $having;




        } else {
            $query1 = "SELECT * FROM Cliente WHERE Id_Cliente=" . $cliente;
            $queryObj->SetQuery($query1);
            $datoscliente = $queryObj->ExecuteQuery('simple');

            $query = ' SELECT T.*, (
                CASE
            WHEN PRG.Codigo_Cum IS NOT NULL THEN "Si"
            WHEN PRG.Codigo_Cum IS  NULL THEN "No"

          END
            ) as Regulado,
            (
                CASE
            WHEN PRG.Codigo_Cum IS NOT NULL THEN PRG.Precio
            WHEN PRG.Codigo_Cum IS  NULL THEN 0
          END
            ) as Precio_Regulado FROM (SELECT C.Nombre as Categoria, C.Separable AS Categoria_Separable, PRD.Id_Categoria,  PRD.Id_Producto,

            IFNULL((IF (IFNULL((SELECT Precio FROM Precio_Regulado WHERE Codigo_Cum=PRD.Codigo_Cum ORDER BY Precio desc LIMIT 1),0) <  LG.Precio AND IFNULL((SELECT Precio FROM Precio_Regulado WHERE Codigo_Cum=PRD.Codigo_Cum ORDER BY Precio desc LIMIT 1),0)>0 , IFNULL((SELECT Precio FROM Precio_Regulado WHERE Codigo_Cum=PRD.Codigo_Cum ORDER BY Precio desc LIMIT 1),0),LG.Precio   )),0) as Precio, 0 as Seleccionado, NULL as Cantidad,

            PRD.Embalaje,SUM(I.Cantidad-(I.Cantidad_Apartada+I.Cantidad_Seleccionada)) as Cantidad_Disponible, CONCAT(PRD.Principio_Activo," ",PRD.Presentacion," ",PRD.Concentracion," ", PRD.Cantidad," ", PRD.Unidad_Medida) as Nombre,  PRD.Nombre_Comercial, PRD.Laboratorio_Comercial,PRD.Laboratorio_Generico,PRD.Codigo_Cum, PRD.Cantidad_Presentacion,

            (
                CASE
                WHEN PRD.Gravado="Si" THEN (SELECT Valor FROM Impuesto WHERE Valor>0 ORDER BY Id_Impuesto DESC LIMIT 1)
                WHEN PRD.Gravado="No"  THEN 0
              END
            ) as Impuesto, I.Costo  as Costo,  SPLIT_STRING(PRD.Codigo_Cum,"-",1) as Cum
            FROM Inventario_Nuevo I
            INNER JOIN Producto PRD
            On I.Id_Producto=PRD.Id_Producto
            INNER JOIN Categoria C ON PRD.Id_Categoria = C.Id_Categoria
            INNER JOIN Producto_Lista_Ganancia LG
            ON PRD.Codigo_Cum = LG.Cum ' . $condicion_principal . $condicion . ' AND LG.Id_Lista_Ganancia =' . $datoscliente['Id_Lista_Ganancia'] . $having . '  ) T left JOIN (SELECT Precio, Codigo_Cum,  SPLIT_STRING(Codigo_Cum,"-",1) as Cum FROM Precio_Regulado group  BY Cum ) PRG ON T.Cum=PRG.Cum ';



        }

        return $query;
    }

    function GetLotesPost($productos, $queryObj, $condicion_principal, $tipo)
    {

        $resultado = [];
        $having = "  HAVING Cantidad>0 ORDER BY I.Fecha_Vencimiento ASC";
        $i = -1;
        $pos = 0;
        foreach ($productos as $value) {
            $i++;
            if ($tipo == 'Cliente') {
                $productos[$i]['Costo'] = $this->GetCostoPost($value['Id_Producto'], $value['Costo'], $queryObj);
            }

            $query1 = "SELECT I.Id_Inventario_Nuevo, I.Id_Producto,I.Lote,(I.Cantidad-(I.Cantidad_Apartada+I.Cantidad_Seleccionada)) as Cantidad,I.Fecha_Vencimiento,$value[Precio] as Precio, 0 as Cantidad_Seleccionada FROM Inventario_Nuevo I
           " . $condicion_principal . " AND I.Id_Producto= $value[Id_Producto] " . $having;


            $queryObj->SetQuery($query1);
            $lotes = $queryObj->ExecuteQuery('Multiple');

            if (count($lotes) > 0) {
                $resultado[$pos] = $productos[$i];
                $resultado[$pos]['Lotes'] = $lotes;
                $pos++;
            } else {
                unset($productos[$i]);
            }

        }

        return $resultado;
    }

    function GetCostoPost($id, $costo, $queryObj)
    {

        $query = "SELECT
        IFNULL(ROUND(AVG(r.Precio)),$costo) AS Costo
        FROM
        (
        SELECT Precio FROM Producto_Acta_Recepcion PR INNER JOIN Acta_Recepcion AR ON PR.Id_Acta_Recepcion=AR.Id_Acta_Recepcion WHERE PR.Id_Producto=$id  AND AR.Id_Bodega!=0 ORDER BY PR.Id_Producto_Acta_Recepcion DESC LIMIT 3
        ) r
        ";

        $queryObj->SetQuery($query);
        $costo = $queryObj->ExecuteQuery('simple');

        return $costo['Costo'];

    }

    public function guardarBorrador()
    {
        $queryObj = new QueryBaseDatos();
        $response = array();
        $http_response = new HttpResponse();

        date_default_timezone_set('America/Bogota');

        $datos = (isset($_REQUEST['datos']) ? $_REQUEST['datos'] : '');
        $codigo = (isset($_REQUEST['codigo']) ? $_REQUEST['codigo'] : '');
        $funcionario = (isset($_REQUEST['funcionario']) ? $_REQUEST['funcionario'] : '');
        $destino = (isset($_REQUEST['destino']) ? $_REQUEST['destino'] : '');


        $query = 'SELECT B.Codigo as Codigo, B.Id_Borrador as Id_Borrador
        FROM Borrador B
        WHERE B.Codigo="' . $codigo . '"';
        $oCon = new consulta();
        $oCon->setQuery($query);
        $respuesta = $oCon->getData();
        unset($oCon);


        if (array_key_exists('Id_Borrador', $respuesta) && ($respuesta != '' || $respuesta != null)) {
            $oItem = new complex("Borrador", "Id_Borrador", $respuesta['Id_Borrador']);
        } else {
            $oItem = new complex("Borrador", "Id_Borrador");
        }
        $oItem->Tipo = "Remision";
        $oItem->Codigo = $codigo;
        $oItem->Texto = $datos;
        $oItem->Id_Funcionario = $funcionario;
        $oItem->Fecha = date("Y-m-d H:i:s");
        $oItem->Nombre_Destino = $destino;
        $oItem->Estado = "Activo";
        $oItem->save();
        unset($oItem);



        $http_response->SetRespuesta(0, 'Guardado Automaticamente', 'Se ha guardado correctamente!');
        $response = $http_response->GetRespuesta();

        return $this->success($response);
    }

    public function eliminarLoteSeleccionado()
    {
        $queryObj = new QueryBaseDatos();
        $response = array();
        $http_response = new HttpResponse();

        $datos = (isset($_REQUEST['datos']) ? $_REQUEST['datos'] : '');
        $datos = (array) json_decode($datos);


        foreach ($datos as $dato) {
            $dato = (array) $dato;
            if ((INT) $dato["Id_Inventario_Nuevo"] != 0) {
                $oItem = new complex("Inventario_Nuevo", "Id_Inventario_Nuevo", (INT) $dato["Id_Inventario_Nuevo"]);
                $actual = $oItem->getData();

                $act = number_format($actual["Cantidad_Seleccionada"], 0, "", "");
                $num = number_format($dato["Cantidad"], 0, "", "");
                $fin = $act - $num;
                if ($fin < 0) {
                    $fin = 0;
                }
                $oItem->Cantidad_Seleccionada = number_format($fin, 0, "", "");
                $oItem->save();
                unset($oItem);
            }

        }

        $http_response->SetRespuesta(0, 'Operacion exitosa', 'Se ha borrado la cantidad seleccionada!');
        $response = $http_response->GetRespuesta();

        return $this->success($response);
    }

    public function comprobarCantidades()
    {
        $http_response = new HttpResponse();
        $queryObj = new QueryBaseDatos();
        $util = new Utility();

        $id_origen = (isset($_REQUEST['id_origen']) ? $_REQUEST['id_origen'] : '');

        $id_producto = (isset($_REQUEST['id_producto']) ? $_REQUEST['id_producto'] : '');
        $id_destino = (isset($_REQUEST['id_destino']) ? $_REQUEST['id_destino'] : '');
        $tipo = (isset($_REQUEST['tipo_origen']) ? $_REQUEST['tipo_origen'] : '');
        $mes = isset($_REQUEST['meses']) ? $_REQUEST['meses'] : '';
        $id_categoria_nueva = isset($_REQUEST['id_categoria_nueva']) ? $_REQUEST['id_categoria_nueva'] : '';

        // echo $id_destino;
        $tipo_destino = isset($_REQUEST['tipo_destino']) ? $_REQUEST['tipo_destino'] : '';

        $grupo = isset($_REQUEST['grupo']) ? $_REQUEST['grupo'] : '';

        $grupo = (array) json_decode($grupo, true);

        if ($mes > '0') {
            $hoy = date("Y-m-t", strtotime(date('Y-m-d')));
            $nuevafecha = strtotime('+' . $mes . ' months', strtotime($hoy));
            $nuevafecha = date('Y-m-d', $nuevafecha);
        } else {
            $nuevafecha = date('Y-m-d');
        }

        $campos = "";

        $condicion_principal = '';

        if ($tipo == 'Bodega') {

            $campos = " B.Id_Bodega_Nuevo, B.Nombre as Grupo,";

            $condicion_principal .= 'INNER JOIN Bodega_Nuevo B ON I.Id_Bodega = B.Id_Bodega_Nuevo
            WHERE B.Id_Bodega_Nuevo = ' . $id_origen;

            if ($tipo_destino != 'Bodega' && (($grupo['Fecha_Vencimiento'] == 'Si' || $grupo['Id_Grupo'] == '0') && $mes != '-1')) {
                $condicion_principal .= "  AND I.Fecha_Vencimiento>='$nuevafecha' ";
            }
        } else if ($tipo == 'Punto_Dispensacion') {
            $campos = " '' as Id_Grupo_Estiba, '' as  Grupo,";
            $condicion_principal .= "
            INNER JOIN Estiba E ON I.Id_Estiba=E.Id_Estiba
            WHERE E.Id_Punto_Dispensacion=$id_origen ";
        }

        if ($condicion_principal == '') {
            $condicion_principal .= " WHERE I.Id_Producto=$id_producto";
        } else {
            $condicion_principal .= " AND I.Id_Producto=$id_producto";
        }

        $condicionContrato = " AND IC.Id_Contrato = '$id_destino'";
        if ($tipo_destino === 'Contrato') {
            $query = 'SELECT SubC.Nombre as Subcategoria,
                    PRD.Id_Subcategoria,
                    SUM(IC.Cantidad - (IC.Cantidad_Apartada+IC.Cantidad_Seleccionada)) as Cantidad_Disponible
                    FROM Inventario_Contrato IC
                    INNER JOIN Inventario_Nuevo I ON I.Id_Inventario_Nuevo = IC.Id_Inventario_Nuevo
                    INNER JOIN Producto PRD
                    ON I.Id_Producto=PRD.Id_Producto
                    INNER JOIN Subcategoria SubC ON PRD.Id_Subcategoria = SubC.Id_Subcategoria
                    ' . $condicion_principal . $condicionContrato;
        } else {
            $query = 'SELECT SubC.Nombre as Subcategoria,
                    PRD.Id_Subcategoria,
                    SUM(I.Cantidad-(I.Cantidad_Apartada+I.Cantidad_Seleccionada)) as Cantidad_Disponible
                    FROM Inventario_Nuevo I
                    INNER JOIN Producto PRD On I.Id_Producto=PRD.Id_Producto
                    INNER JOIN Subcategoria SubC ON PRD.Id_Subcategoria = SubC.Id_Subcategoria
                    ' . $condicion_principal;
        }
        // echo $query; exit;
        $queryObj->SetQuery($query);
        $productos = $queryObj->ExecuteQuery('Multiple');
        $productos = $this->GetLotesComprobarCantidades($productos, $queryObj, $condicion_principal, $tipo, $tipo_destino, $campos);

        return $this->success($productos);
    }


    private function GetLotesComprobarCantidades($productos, $queryObj, $condicion_principal, $tipo, $tipo_destino, $campos)
    {

        $resultado = [];
        $having = "  HAVING Cantidad>0 ORDER BY I.Fecha_Vencimiento ASC";
        $i = -1;
        $pos = 0;
        $condicionBodega = '';
        if ($tipo == 'Bodega') {
            $condicionBodega .= '
        INNER JOIN Producto PRD
        On I.Id_Producto=PRD.Id_Producto
        INNER JOIN Subcategoria SubC ON PRD.Id_Subcategoria = SubC.Id_Subcategoria ';
        }

        foreach ($productos as $value) {
            $i++;

            if ($tipo_destino === 'Contrato') {
                $query1 = "SELECT I.Id_Inventario_Nuevo, I.Id_Producto,I.Lote,IC.Id_Inventario_Contrato, I.Fecha_Vencimiento,

                            $campos
                             (IC.Cantidad-(IC.Cantidad_Apartada+IC.Cantidad_Seleccionada)) as Cantidad, 0 as Cantidad_Seleccionada
                       FROM Inventario_Contrato IC
                       INNER JOIN Inventario_Nuevo I ON I.Id_Inventario_Nuevo = IC.Id_Inventario_Nuevo
                       " . $condicionBodega . $condicion_principal . $having;
            } else {
                $query1 = "SELECT I.Id_Inventario_Nuevo, I.Id_Producto,I.Lote,
                            $campos
                              (I.Cantidad-(I.Cantidad_Apartada+I.Cantidad_Seleccionada)) as Cantidad,
                              I.Fecha_Vencimiento,0 as Cantidad_Seleccionada FROM Inventario_Nuevo I
                              $condicionBodega  $condicion_principal
                               $having";
            }
            $queryObj->SetQuery($query1);
            $lotes = $queryObj->ExecuteQuery('Multiple');

            if (count($lotes) > 0) {
                $resultado[$pos] = $productos[$i];
                $resultado[$pos]->Lotes = $lotes;
                $pos++;
            } else {
                unset($productos[$i]);
            }
        }

        return $resultado;
    }

    public function eliminarLotesMasivos()
    {
        $queryObj = new QueryBaseDatos();
        $response = array();
        $http_response = new HttpResponse();

        $datos = (isset($_REQUEST['datos']) ? $_REQUEST['datos'] : '');
        $datos = json_decode($datos, true);




        foreach ($datos as $value) {
            foreach ($value['Lotes_Seleccionados'] as $lote) {
                $lote = (array) $lote;
                if ((INT) $lote["Id_Inventario_Nuevo"] != 0) {

                    $oItem = new complex("Inventario_Nuevo", "Id_Inventario_Nuevo", (INT) $lote["Id_Inventario_Nuevo"]);
                    $actual = $oItem->getData();

                    $act = number_format($actual["Cantidad_Seleccionada"], 0, "", "");
                    $num = number_format($lote["Cantidad"], 0, "", "");
                    $fin = $act - $num;
                    if ($fin < 0) {
                        $fin = 0;
                    }
                    $oItem->Cantidad_Seleccionada = number_format($fin, 0, "", "");
                    $oItem->save();
                    unset($oItem);
                }

            }
        }



        $http_response->SetRespuesta(0, 'Operacion exitosa', 'Se ha borrado la cantidad seleccionada!');
        $response = $http_response->GetRespuesta();

        return $this->success($response);
    }

    public function guardarHoraInicio(Request $request)
    {
        $id = $request->input('id', '');
        $funcionario = $request->input('funcionario', '');
        $tipo = $request->input('tipo', '');
        $mod = $request->input('mod', '');

        //remision - devolucion_compra
        $id_mod = 'Id_' . $mod;

        $oItem = new complex($mod, $id_mod, $id);
        $remision = $oItem->getData();
        unset($oItem);



        $oItem = new complex($mod, $id_mod, $id);
        if ($tipo == "Fase1") {
            $oItem->Fase_1 = $funcionario;
            $oItem->Inicio_Fase1 = date("Y-m-d H:i:s");
        } elseif ($tipo == "Fase2") {
            $oItem->Fase_2 = $funcionario;
            $oItem->Inicio_Fase2 = date("Y-m-d H:i:s");
        }
        $oItem->save();
        unset($oItem);

        $resultado = "Echo";

        return $this->success($resultado);
    }

    public function guardarGuiaRemisiond()
    {
        $datos = (isset($_REQUEST['datos']) ? $_REQUEST['datos'] : '');


        $datos = (array) json_decode($datos);
        if ($datos['Tipo_Rem'] != 'Devolucion') {
            # code...


            $oItem = new complex("Remision", 'Id_Remision', $datos["Id_Remision"]);
            $estado = $oItem->Estado;
            $oItem->Estado = $estado == "Facturada" ? "Facturada" : "Enviada";
            $oItem->Guia = strtoupper($datos["Numero_Guia"]);
            $oItem->Empresa_Envio = strtoupper($datos["Empresa_Envio"]);
            $oItem->save();
            $remision = $oItem->getData();
            unset($oItem);

            //Guardar actividad de la remision

            $oItem = new complex('Actividad_Remision', "Id_Actividad_Remision");
            $oItem->Id_Remision = $datos["Id_Remision"];
            $oItem->Identificacion_Funcionario = $datos['Identificacion_Funcionario'];
            if ($datos['Tipo'] == "Creacion") {
                $oItem->Detalles = "Se envia la Remision " . $remision["Codigo"] . " con el Numero de guia " . $datos["Numero_Guia"] . " con la empresa " . $datos["Empresa_Envio"];
            } else {
                $oItem->Detalles = "Se modifico la guia de la remision " . $remision["Codigo"] . " al Numero de guia " . $datos["Numero_Guia"] . " y empresa " . $datos["Empresa_Envio"];
            }
            $oItem->Fecha = date("Y-m-d H:i:s");
            $oItem->Estado = "Enviada";
            $oItem->save();
            unset($oItem);
        } else {

            $oItem = new complex("Devolucion_Compra", 'Id_Devolucion_Compra', $datos["Id_Remision"]);

            $oItem->Estado = "Enviada";
            $oItem->Guia = strtoupper($datos["Numero_Guia"]);
            $oItem->Empresa_Envio = strtoupper($datos["Empresa_Envio"]);
            $oItem->save();
            $remision = $oItem->getData();
            unset($oItem);

            //Guardar actividad de la remision

            $oItem = new complex('Actividad_Devolucion_Compra', "Id_Actividad_Devolucion_Compra");
            $oItem->Id_Devolucion_Compra = $datos["Id_Remision"];
            $oItem->Identificacion_Funcionario = $datos['Identificacion_Funcionario'];
            if ($datos['Tipo'] == "Creacion") {
                $oItem->Detalles = "Se envia la Remision " . $remision["Codigo"] . " con el Numero de guia " . $datos["Numero_Guia"] . " con la empresa " . $datos["Empresa_Envio"];
            } else {
                $oItem->Detalles = "Se modifico la guia de la remision " . $remision["Codigo"] . " al Numero de guia " . $datos["Numero_Guia"] . " y empresa " . $datos["Empresa_Envio"];
            }
            $oItem->Fecha = date("Y-m-d H:i:s");
            $oItem->Estado = "Enviada";
            $oItem->save();
            unset($oItem);
        }



        $resultado['mensaje'] = "Se ha guardado correctamente el n&uacute;mero de gu&iacute;a de la Remision con codigo: " . $remision['Codigo'];
        $resultado['tipo'] = "success";
        return $this->success($resultado);
    }

    public function balanza()
    {
        $balance = CompanyConfiguration::where('company_id', getCompanyWorkedId())->first()->balance;
        $res = true;
        if ($balance == '0') {
            $res = false;
        }

        return $this->success($res);
    }

    public function remisionPhp()
    {

        $id = (isset($_REQUEST['id']) ? $_REQUEST['id'] : '');

        $query = 'SELECT R.*
        FROM Remision R
        WHERE R.Id_Remision=' . $id;
        $oCon = new consulta();
        //$oCon->setTipo('Multiple');
        $oCon->setQuery($query);
        $remision = $oCon->getData();
        unset($oCon);



        $variableOrigen = $this->variableOrigen($remision);

        $query = 'SELECT *
        FROM ' . $variableOrigen . '
        WHERE Id_' . $variableOrigen . '=' . $remision['Id_Origen'];
        $oCon = new consulta();
        //$oCon->setTipo('Multiple');
        $oCon->setQuery($query);
        $origen = $oCon->getData();
        unset($oCon);


        $variableDestino = $this->variableDestino($remision);
        $query = 'SELECT *
        FROM ' . $variableDestino . '
        WHERE Id_' . $variableDestino . '=' . $remision['Id_Destino'];
        $oCon = new consulta();
        $oCon->setQuery($query);
        $destino = $oCon->getData();
        unset($oCon);


        if ($remision['Tipo_Lista'] == "Contrato") {
            $oItem = new complex('Contrato', 'Id_Contrato', $remision['Id_Lista']);
            $contrato = $oItem->getData();
            $resultado['Contrato'] = $contrato;
            unset($oItem);
        } elseif ($remision['Tipo_Lista'] == "Lista_Ganancia") {
            $oItem = new complex('Lista_Ganancia', 'Id_Lista_Ganancia', $remision['Id_Lista']);
            $contrato = $oItem->getData();
            $resultado['Lista'] = $contrato;
            unset($oItem);
        }

        $resultado['Remision'] = $remision;
        $resultado['Origen'] = $origen;
        $resultado['Destino'] = $destino;

        return $this->success($resultado);
    }

    private function variableOrigen($remision)
    {

        if ($remision['Tipo_Origen'] == 'Bodega') {
            $variable = 'Bodega_Nuevo';
        } else {
            $variable = $remision['Tipo_Origen'];
        }
        return $variable;
    }

    private function variableDestino($remision)
    {

        if ($remision['Tipo_Destino'] == 'Bodega') {
            $variable = 'Bodega_Nuevo';
        } else {
            $variable = $remision['Tipo_Destino'];
        }
        return $variable;
    }

    public function productosRemisionAlistamiento()
    {

        $id = (isset($_REQUEST['id']) ? $_REQUEST['id'] : '');
        $tipo = (isset($_REQUEST['tipo']) ? $_REQUEST['tipo'] : '');

        if ($tipo == 'Devolucion') {
            $desde = 'Devolucion_Compra';
        } else {
            $desde = 'Remision';
        }
        $query = 'SELECT SubC.Id_Categoria_Nueva, SubC.Nombre as Subcategoria,
    PR.Lote, PR.Cantidad, PR.Fecha_Vencimiento, PR.Id_Producto,
    PR.Id_Inventario_Nuevo, /*PR.Id_Producto_Remision, */
    P.Nombre_Comercial,P.Embalaje, P.Laboratorio_Comercial, P.Laboratorio_Generico,
    P.Peso_Presentacion_Minima, P.Peso_Presentacion_Regular, P.Imagen,
    P.Peso_Presentacion_Maxima,P.Codigo_Barras,P.Presentacion, P.Id_Subcategoria,
        IFNULL(CONCAT(P.Principio_Activo, " ",
            P.Presentacion, " ",
            P.Concentracion, " - ",
            P.Cantidad," ",
            P.Unidad_Medida), P.Nombre_Comercial) AS Nombre_Producto,

    I.Alternativo,
    E.Nombre AS Nombre_Estiba, E.Id_Estiba,
    (CEIL((PR.Cantidad/P.Cantidad_Presentacion)*P.Peso_Presentacion_Regular)) as Peso_Total

    FROM Producto_' . $desde . ' PR
    INNER JOIN Producto P ON PR.Id_Producto=P.Id_Producto
    INNER JOIN Inventario_Nuevo I ON PR.Id_Inventario_Nuevo=I.Id_Inventario_Nuevo
    INNER JOIN Estiba E ON E.Id_Estiba = I.Id_Estiba
    LEFT JOIN Subcategoria SubC ON SubC.Id_Subcategoria = P.Id_Subcategoria
    WHERE PR.Id_' . $desde . ' =' . $id . '
    ORDER BY E.Nombre DESC, Nombre_Producto ASC';

        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->setTipo('Multiple');
        $productos = $oCon->getData();
        unset($oCon);

        $peso_general = 0;

        $i = -1;
        foreach ($productos as $producto) {
            $i++;

            $productos[$i]->Tolerancia_Individual = (int) 0;
            $peso_general += (int) $producto->Peso_Total;

            $productos[$i]->Codigo_Ingresado = "";
            $productos[$i]->Peso_Ingresado = "";
            $productos[$i]->Habilitado = "true";
            $productos[$i]->Clase = "blur";
            $productos[$i]->Validado = false;
            $productos[$i]->Codigo_Validado = false;
        }

        $productos = $this->separarPorEstiba($productos);
        $resultado["Productos"] = $productos;
        $resultado["Peso_General"] = $peso_general;
        $resultado["Tolerancia_Global"] = 0;
        return $this->success($resultado);
    }

    private function separarPorEstiba($productos)
    {
        // Inicializar el contador de cantidad
        $cantidad = 0;

        // Iterar sobre los productos
        foreach ($productos as $key => $producto) {
            // Verificar si es el primer producto o si la estiba es diferente al producto anterior
            if ($key === 0 || $producto->Nombre_Estiba !== $productos[$key - 1]->Nombre_Estiba) {
                // Reiniciar el contador de cantidad
                $cantidad = 0;

                // Iterar sobre los productos para contar la cantidad de productos con la misma estiba
                foreach ($productos as $productoComparacion) {
                    if ($producto->Nombre_Estiba === $productoComparacion->Nombre_Estiba) {
                        $cantidad++;
                    }
                }

                // Asignar la cantidad de productos de la estiba al producto actual
                $productos[$key]->Cantidades_Productos_Estiba = $cantidad;
            }
        }

        return $productos;
    }

    public function consultaCodigoEstiba()
    {
        $codigo = (isset($_REQUEST['codigo']) ? $_REQUEST['codigo'] : false);
        $id = (isset($_REQUEST['id']) ? $_REQUEST['id'] : false);
        $codigo1 = substr($codigo, 0, 12);

        $query = 'SELECT  Id_Estiba
        FROM Estiba
        WHERE Id_Estiba=' . $id . ' AND Codigo_Barras LIKE "%' . $codigo1 . '%"';

        $oCon = new consulta();
        $oCon->setQuery($query);
        //$oCon->setTipo('Multiple');
        $resultado = $oCon->getData();
        unset($oCon);

        return $this->success($resultado);
    }

    public function guardarFase1()
    {
        $mod = (isset($_REQUEST['modulo']) ? $_REQUEST['modulo'] : '');
        $id = (isset($_REQUEST['id']) ? $_REQUEST['id'] : '');

        $funcionario = (isset($_REQUEST['funcionario']) ? $_REQUEST['funcionario'] : '');

        $id_mod = 'Id_' . $mod;

        $oItem = new complex($mod, $id_mod, $id);
        $oItem->Estado_Alistamiento = 1;
        $oItem->Fin_Fase1 = date("Y-m-d H:i:s");
        $oItem->save();
        unset($oItem);

        $oItem = new complex($mod, $id_mod, $id);
        $remision = $oItem->getData();
        unset($oItem);

        //Guardar actividad de la remision
        $oItem = new complex('Actividad_' . $mod, "Id_Actividad_" . $mod);
        $oItem->$id_mod = $id;
        $oItem->Identificacion_Funcionario = $funcionario;
        $oItem->Detalles = "Se realizo la Fase 1 de Alistamiento de la Remision " . $remision["Codigo"];
        $oItem->Estado = "Fase 1";
        $oItem->Fecha = date("Y-m-d H:i:s");
        $oItem->save();
        unset($oItem);

        $resultado['mensaje'] = "Se ha guardado correctamente la Fase 1 de la Remision con codigo: " . $remision['Codigo'];
        $resultado['tipo'] = "success";
        return $this->success($resultado);
    }

    public function entregaPendientesPDF()
    {
        $id = (isset($_REQUEST['id']) ? $_REQUEST['id'] : '');

        /* DATOS DEL ARCHIVO A MOSTRAR */
        $oItem = new complex('Remision', "Id_Remision", $id);
        $data = $oItem->getData();
        unset($oItem);
        /* FIN DATOS DEL ARCHIVO A MOSTRAR */
        $query = 'SELECT (SELECT CONCAT(F.first_name," ",F.first_surname) FROM people F WHERE F.identifier=PR.Identificacion_Funcionario) as Funcionario FROM Producto_Descarga_Pendiente_Remision PR WHERE PR.Id_Remision=' . $id . ' LIMIT 1';
        $oCon = new consulta();
        $oCon->setQuery($query);
        $rem = $oCon->getData();
        unset($oCon);

        $query = 'SELECT PR.Lote, CONCAT(P.Principio_Activo," ",P.Presentacion," ",P.Concentracion, P.Cantidad," ", P.Unidad_Medida, " ") AS Nombre_Producto,P.Nombre_Comercial, PR.Cantidad, PR.Cantidad, P.Laboratorio_Generico, P.Embalaje,
    (SELECT CONCAT(P.Id_Paciente," - ",P.Primer_Nombre," ",Primer_Apellido," ",P.Segundo_Apellido) FROM Paciente P WHERE P.Id_Paciente=PR.Id_Paciente ) as Paciente, (SELECT D.Codigo FROM Dispensacion D WHERE D.Id_Dispensacion=PR.Id_Dispensacion) as DIS
    FROM Producto_Descarga_Pendiente_Remision PR
    INNER JOIN Producto P ON PR.Id_Producto=P.Id_Producto
    WHERE PR.Id_Remision=' . $id . ' ORDER BY Nombre_Comercial';

        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->setTipo('Multiple');
        $productos = $oCon->getData();
        unset($oCon);

        if ($data["Tipo_Origen"] == 'Bodega') {
            $data["Tipo_Origen"] .= '_Nuevo';
        }
        if ($data["Tipo_Destino"] == 'Bodega') {
            $data["Tipo_Destino"] .= '_Nuevo';
        }

        if ($data["Tipo_Origen"] == 'Cliente') {
            $data["Tipo_Origen"] = 'third_parties';
            $oItem = new complex($data["Tipo_Origen"], 'id', $data["Id_Origen"]);
            $origen = $oItem->getData();
            unset($oItem);
        } else {
            $oItem = new complex($data["Tipo_Origen"], "Id_" . $data["Tipo_Origen"], $data["Id_Origen"]);
            $origen = $oItem->getData();
            unset($oItem);
        }
        if ($data["Tipo_Destino"] == 'Cliente') {
            $data["Tipo_Destino"] = 'third_parties';
            $oItem = new complex($data["Tipo_Destino"], 'id', $data["Id_Destino"]);
            $destino = $oItem->getData();
            unset($oItem);
        } else {
            $oItem = new complex($data["Tipo_Destino"], "Id_" . $data["Tipo_Destino"], $data["Id_Destino"]);
            $destino = $oItem->getData();
            unset($oItem);
        }


        $oItem = new complex('people', "identifier", $data["Identificacion_Funcionario"]);
        $elabora = $oItem->getData();
        unset($oItem);

        $fecha = $data['Fecha'];

        $header = (object) [
            'Titulo' => '',
            'Codigo' => $data["Codigo"] ?? '',
            'Fecha' => $fecha,
            'CodigoFormato' => $data->format_code ?? '',
        ];

        $pdf = Pdf::loadView('pdf.entrega_pendientes', [
            'data' => $data,
            'datosCabecera' => $header,
            'origen' => $origen,
            'destino' => $destino,
            'elabora' => $elabora,
            'productos' => $productos,
            'rem' => $rem
        ]);

        return $pdf->stream($data["Codigo"]);
    }

    public function alistamientoProductoRemision()
    {

        $id = (isset($_REQUEST['id']) ? $_REQUEST['id'] : '');

        $query = 'SELECT C.Id_Categoria_Nueva, C.Nombre as Categoria, PR.Lote, PR.Cantidad, P.Nombre_Comercial, P.Nombre_Comercial AS Nombre_Producto, PR.Fecha_Vencimiento, PR.Id_Producto,P.Codigo_Barras,I.Alternativo, P.Id_Categoria, PR.Id_Inventario, PR.Id_Producto_Remision, P.Imagen
        FROM Producto_Remision PR
        INNER JOIN Producto P
        ON PR.Id_Producto=P.Id_Producto
        INNER JOIN Inventario_Nuevo I
        ON PR.Id_Inventario=I.Id_Inventario_Nuevo
        LEFT JOIN Categoria_Nueva C
        ON C.Id_Categoria_Nueva = P.Id_Categoria
        WHERE PR.Id_Remision =' . $id . '
        ORDER BY P.Id_Categoria DESC, Nombre_Producto ASC';

        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->setTipo('Multiple');
        $productos = $oCon->getData();
        unset($oCon);

        $i = -1;
        foreach ($productos as $producto) {
            $i++;
            if ($i == 0) {
                $productos[$i]->Habilitado = "false";
                $productos[$i]->Clase = "noblur";
                $productos[$i]->Validado = false;
                $productos[$i]->Codigo_Validado = false;
            } else {
                $productos[$i]->Habilitado = "true";
                $productos[$i]->Clase = "blur";
                $productos[$i]->Validado = false;
                $productos[$i]->Codigo_Validado = false;
            }


        }
        $resultado["Productos"] = $productos;
        return $this->success($resultado);
    }

    public function consultaCodigo()
    {
        $codigo = (isset($_REQUEST['codigo']) ? $_REQUEST['codigo'] : false);
        $id = (isset($_REQUEST['id']) ? $_REQUEST['id'] : false);
        $codigo1 = substr($codigo, 0, 12);

        $query = 'SELECT  P.Codigo_Barras, PR.Id_Producto
                FROM Producto_Remision PR
                INNER JOIN Producto P
                ON PR.Id_Producto=P.Id_Producto
                INNER JOIN Inventario_Nuevo I
                ON PR.Id_Inventario=I.Id_Inventario_Nuevo
                WHERE PR.Id_Producto=' . $id . ' AND (I.Codigo="' . $codigo1 . '" OR P.Codigo_Barras="' . $codigo . '" OR I.Alternativo LIKE "%' . $codigo1 . '%")';

        $oCon = new consulta();
        $oCon->setQuery($query);
        $resultado = $oCon->getData();
        unset($oCon);

        return $this->success($resultado);
    }

    public function guardarFase1Post()
    {
        $mod = (isset($_REQUEST['modulo']) ? $_REQUEST['modulo'] : '');
        $id = (isset($_REQUEST['id']) ? $_REQUEST['id'] : '');
        $funcionario = (isset($_REQUEST['funcionario']) ? $_REQUEST['funcionario'] : '');
        $oItem = new complex($mod, 'Id_Remision', $id);
        $oItem->Estado_Alistamiento = 1;
        $oItem->Fin_Fase1 = date("Y-m-d H:i:s");

        $oItem->save();
        unset($oItem);

        $oItem = new complex($mod, 'Id_Remision', $id);
        $remision = $oItem->getData();
        unset($oItem);

        $oItem = new complex('Actividad_Remision', "Id_Actividad_Remision");
        $oItem->Id_Remision = $id;
        $oItem->Identificacion_Funcionario = $funcionario;
        $oItem->Detalles = "Se realizo la Fase 1 de Alistamiento de la Remision " . $remision["Codigo"];
        $oItem->Estado = "Fase 1";
        $oItem->Fecha = date("Y-m-d H:i:s");
        $oItem->save();
        unset($oItem);

        $resultado['mensaje'] = "Se ha guardado correctamente la Fase 1 de la Remision con codigo: " . $remision['Codigo'];
        $resultado['tipo'] = "success";
        return $this->success($resultado);
    }

    public function guardarFase2Post()
    {

        $mod = (isset($_REQUEST['modulo']) ? $_REQUEST['modulo'] : '');
        $id = (isset($_REQUEST['id']) ? $_REQUEST['id'] : '');
        $funcionario = (isset($_REQUEST['funcionario']) ? $_REQUEST['funcionario'] : '');
        $productos = (isset($_REQUEST['productos']) ? $_REQUEST['productos'] : '');
        $peso = (isset($_REQUEST['peso']) ? $_REQUEST['peso'] : '');

        if (empty($peso) || $peso == 'undefined') {
            $peso = 0;
        }
        $productos = (array) json_decode($productos, true);
        //$peso = (array) json_decode($peso , true);
//var_dump($_REQUEST);
        $oItem = new complex($mod, 'Id_Remision', $id);
        $oItem->Estado_Alistamiento = 2;
        $oItem->Fin_Fase2 = date("Y-m-d H:i:s");
        $oItem->Estado = "Alistada";
        $oItem->Peso_Remision = $peso;
        $oItem->save();
        unset($oItem);

        $oItem = new complex($mod, 'Id_Remision', $id);
        $remision = $oItem->getData();
        unset($oItem);

        //Guardar actividad de la remision
        $oItem = new complex('Actividad_Remision', "Id_Actividad_Remision");
        $oItem->Id_Remision = $id;
        $oItem->Identificacion_Funcionario = $funcionario;
        $oItem->Detalles = "Se realizo la Fase 2 de Alistamiento de la Remision " . $remision["Codigo"];
        $oItem->Estado = "Fase 2";
        $oItem->Fecha = date("Y-m-d H:i:s");
        $oItem->save();
        unset($oItem);

        foreach ($productos as $producto) {

            $oItem = new complex('Inventario_Nuevo', 'Id_Inventario_Nuevo', $producto["Id_Inventario"]);
            $inv = $oItem->getData();
            $apartada = number_format($inv["Cantidad_Apartada"], 0, "", "");
            $cantidad = number_format($inv["Cantidad"], 0, "", "");
            $actual = number_format($producto["Cantidad"], 0, "", "");

            $fin = $apartada - $actual;
            $final = $cantidad - $actual;
            if ($fin < 0) {
                $fin = 0;
            }
            if ($final < 0) {
                $final = 0;
            }
            $oItem->Cantidad_Apartada = number_format($fin, 0, "", "");
            $oItem->Cantidad = number_format($final, 0, "", "");
            $oItem->save();
            unset($oItem);

        }
        $resultado['mensaje'] = "Se ha guardado correctamente la Fase 2 de la Remision con codigo: " . $remision['Codigo'];
        $resultado['tipo'] = "success";

        return $this->success($resultado);
    }

    private function obtenerProductosConVariables($productosIds)
    {
        // Obtener productos con sus variables y etiquetas asociadas
        $productos = Product::whereIn('Id_Producto', $productosIds)
            ->with(['variableProducts.categoryVariable'])
            ->get();

        $variablesLabels = [];
        foreach ($productos as $producto) {
            $variables = [];
            foreach ($producto->variableProducts as $variableProduct) {
                $variables[$variableProduct->categoryVariable->label] = $variableProduct->valor;
                $variablesLabels[] = $variableProduct->categoryVariable->label;
            }
            $producto->variables = $variables;
        }

        $collection = collect($variablesLabels);
        $variablesLabels = $collection->unique();


        return [$productos, $variablesLabels];
    }

    public function seleccionarLotesInventario()
    {
        $queryObj = new QueryBaseDatos();
        $response = array();
        $http_response = new HttpResponse();


        $datos = (isset($_REQUEST['datos']) ? $_REQUEST['datos'] : '');
        $datos = (array) json_decode($datos);

        $oItem = new complex("Inventario_Nuevo", "Id_Inventario_Nuevo", $datos["Id_Inventario_Nuevo"]);
        $cantidad_seleccionada = ($oItem->Cantidad_Seleccionada - $datos["Cantidad_Seleccionada"]) + $datos["Cantidad"];
        if ($cantidad_seleccionada < 0) {
            $cantidad_seleccionada = 0;
        }

        $oItem->Cantidad_Seleccionada = number_format($cantidad_seleccionada, 0, "", "");
        $oItem->save();
        unset($oItem);

        $http_response->SetRespuesta(0, 'Registro Exitoso', 'Se ha guardado la cantidad seleccionada!');
        $response = $http_response->GetRespuesta();

        echo json_encode($response);
    }

    public function datosBodegasPuntos(Request $request)
    {
        $id = $request->input('id');

        $bodega = FuncionarioBodegaNuevo::select('B.Nombre as text', DB::raw('CONCAT("B-", B.Id_Bodega_Nuevo) as value'))
            ->join('Bodega_Nuevo as B', 'Funcionario_Bodega_Nuevo.Id_Bodega_Nuevo', '=', 'B.Id_Bodega_Nuevo')
            ->where('Funcionario_Bodega_Nuevo.Identificacion_Funcionario', $id)
            ->get();

        $punto = People::select(DB::raw('CONCAT("P-", people.dispensing_point_id) as value'), 'PD.Nombre as text')
            ->join('Punto_Dispensacion as PD', 'people.dispensing_point_id', '=', 'PD.Id_Punto_Dispensacion')
            ->where('people.id', $id)
            ->get();

        $resultado["Bodega"] = $bodega;
        $resultado["Punto"] = $punto;

        return response()->json($resultado);
    }
}
