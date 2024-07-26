<?php

namespace App\Http\Controllers;

use App\Exports\ReporteVencimientosExport;
use App\Http\Services\complex;
use App\Http\Services\consulta;
use App\Http\Services\Contabilizar;
use App\Models\BodegaNuevo;
use Exception;
use App\Models\Bodegas;
use App\Models\GrupoEstiba;
use App\Models\Estiba;
use App\Models\InventarioNuevo;
use App\Models\OrdenCompraNacional;
use App\Models\Person;
use App\Models\ProductoOrdenCompraNacional;
use App\Models\PuntoDispensacion;
use App\Models\PurchaseRequest;
use App\Models\Remision;
use Illuminate\Http\Request;
use App\Traits\ApiResponser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class BodegasController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $bodegas = Bodegas::with('grupo_estibas')
            ->where('company_id', getCompanyWorkedId())
            ->get();

        return $this->success($bodegas);
    }


    public function bodegasConGrupos($id)
    {
        return $this->success(
            GrupoEstiba::where('Id_Bodega_Nuevo', $id)
                ->when(request()->get('Nombre'), function ($q, $fill) {
                    $q->where('Nombre', 'like', '%' . $fill . '%');
                })
                ->when(request()->get('Fecha_Vencimiento'), function ($q, $fill) {
                    $q->where('Fecha_Vencimiento', '=', $fill);
                })
                ->when(request()->get('Presentacion'), function ($q, $fill) {
                    $q->where('Presentacion', '=', $fill);
                })->paginate(request()->get('pageSize', 5), ['*'], 'page', request()->get('page', 1))
        );
    }

    public function gruposConEstibas($id)
    {
        return $this->success(
            Estiba::where('Id_Grupo_Estiba', $id)
                ->when(request()->get('Nombre'), function ($q, $fill) {
                    $q->where('Nombre', 'like', '%' . $fill . '%');
                })->when(request()->get('Estado'), function ($q, $fill) {
                    $q->where('Estado', '=', $fill);
                })->when(request()->get('Codigo_Barras'), function ($q, $fill) {
                    $q->where('Codigo_Barras', 'like', '%' . $fill . '%');
                })->paginate(request()->get('pageSize', 5), ['*'], 'page', request()->get('page', 1))
        );
    }

    public function paginate()
    {
        return $this->success(
            Bodegas::with('municipality')
                ->where('company_id', getCompanyWorkedId())
                ->when(request()->get('Nombre'), function ($q, $fill) {
                    $q->where('Nombre', 'like', '%' . $fill . '%');
                })->when(request()->get('Direccion'), function ($q, $fill) {
                    $q->where('Direccion', 'like', '%' . $fill . '%');
                })->when(request()->get('Telefono'), function ($q, $fill) {
                    $q->where('Telefono', 'like', '%' . $fill . '%');
                })->when(request()->get('Compra_Internacional'), function ($q, $fill) {
                    $q->where('Compra_Internacional', '=', $fill);
                })->paginate(request()->get('pageSize', 10), ['*'], 'page', request()->get('page', 1))
        );
    }


    public function activarInactivar()
    {

        switch (request("modulo")) {
            case 'bodega':
                return $this->success(Bodegas::where('Id_Bodega_Nuevo', request()->get('id'))
                    ->update(['Estado' => request()->get('state')]));
            case 'grupo':
                return $this->success(GrupoEstiba::where('Id_Grupo_Estiba', request()->get('id'))
                    ->update(['Estado' => request()->get('state')]));
        }
    }

    public function impuestos()
    {
        return $this->success(DB::table("Impuesto")->get());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */


    public function store()
    {
        try {
            $validator = Validator::make(request()->all(), [
                'nombre' => 'required|string|max:255',
                'direccion' => 'required|string|max:255',
                'telefono' => 'required|string|max:20',
                'typeMapa' => 'nullable|string|in:jpeg,jpg,png',
                'mapa' => 'required_if:typeMapa,jpeg,jpg,png',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse($validator->errors()->toArray());
            }

            $camposPorRegistrar = [
                'Nombre' => request()->get('nombre'),
                'Direccion' => request()->get('direccion'),
                'Telefono' => request()->get('telefono'),
                'Compra_Internacional' => request()->get('compraInternacional'),
                'municipality_id' => request()->get('municipality_id'),
                'company_id' => getCompanyWorkedId(),
            ];

            $type = '.' . request()->get('typeMapa');
            if (!is_null(request("typeMapa"))) {
                if (in_array(request("typeMapa"), ['jpeg', 'jpg', 'png'])) {
                    $base64 = saveBase64(request("mapa"), 'mapas_bodegas/', true);
                    $url = URL::to('/') . '/api/image?path=' . $base64;
                } else {
                    throw new Exception(
                        "No se ha encontrado un formato de imagen válido (" . request("typeMapa") . "), revise e intente nuevamente"
                    );
                }
                $camposPorRegistrar['Mapa'] = $url;
            }

            $value = Bodegas::updateOrCreate(['Id_Bodega_Nuevo' => request()->get('id')], $camposPorRegistrar);
            return ($value->wasRecentlyCreated) ? $this->success('Creado con éxito') : $this->success('Actualizado con éxito');
        } catch (\Throwable $th) {
            return $this->errorResponse(["file" => $th->getFile(), "text" => $th->getMessage()]);
        }
    }


    public function storeGrupo()
    {
        try {
            $value = GrupoEstiba::updateOrCreate(['Id_Grupo_Estiba' => request()->get('id')], [
                'Nombre' => request()->get('nombre'),
                'Presentacion' => request()->get('presentacion'),
                'Fecha_Vencimiento' => request()->get('fechaVencimiento'),
                'Id_Bodega_Nuevo' => request()->get('idBodega')
            ]);
            return ($value->wasRecentlyCreated) ? $this->success('Creado con éxito') : $this->success('Actualizado con éxito');
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getFile() . $th->getMessage());
        }
    }

    public function storeEstiba()
    {
        try {
            $value = Estiba::updateOrCreate(['Id_Estiba' => request()->get('id')], [
                'Nombre' => request()->get('nombre'),
                'Id_Grupo_Estiba' => request()->get('idGrupo'),
                'Id_Bodega_Nuevo' => request()->get('idBodega'),
                'Codigo_Barras' => request()->get('codigoBarras'),
                'Estado' => request()->get('estado')
            ]);
            return ($value->wasRecentlyCreated) ? $this->success('Creado con éxito') : $this->success('Actualizado con éxito');
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getFile() . $th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        return $this->success(Bodegas::where('Id_Bodega_Nuevo', $id)->first());
    }

    public function getBodegas()
    {
        $bodegas = Bodegas::where('company_id', getCompanyWorkedId())->get();

        if ($bodegas->isNotEmpty()) {
            $resultado["Mensaje"] = 'Bodegas encontradas con éxito';
            $resultado["Tipo"] = "success";
            $resultado["Bodegas"] = $bodegas;
        } else {
            $resultado["Tipo"] = "error";
            $resultado["Titulo"] = "Error al intentar buscar las bodegas";
            $resultado["Texto"] = "Ha ocurrido un error inesperado.";
        }


        return response()->json($resultado);
    }

    public function bodegaPunto(Request $request)
    {
        $tipo = $request->input('tipo', false);

        if ($tipo == "Bodega") {
            $resultados = BodegaNuevo::select('Id_Bodega_Nuevo as Id', 'Nombre')
                ->orderBy('Nombre')
                ->get();
        } else {
            $resultados = PuntoDispensacion::select('Id_Punto_Dispensacion as Id', 'Nombre')
                ->orderBy('Nombre')
                ->get();
        }

        return response()->json($resultados);

    }

    public function vencimientos(Request $request)
    {
        $year = $request->input('year', '');
        $tipo = $request->input('tipo', false);
        $id_bodega_punto = $request->input('id_bodega_punto', false);

        $condicion = '';

        if ($tipo == 'Bodega') {
            if ($id_bodega_punto != 'todos') {
                $condicion .= " WHERE B.Id_Bodega_Nuevo=$id_bodega_punto";
            } else {
                $condicion .= " WHERE B.Id_Bodega_Nuevo!=0";
            }
        } else {
            if ($id_bodega_punto != 'todos') {
                $condicion .= " WHERE E.Id_Punto_Dispensacion=$id_bodega_punto";
            } else {
                $condicion .= " WHERE E.Id_Punto_Dispensacion!=0";
            }
        }

        $meses = [
            '01-Enero',
            '02-Febrero',
            '03-Marzo',
            '04-Abril',
            '05-Mayo',
            '06-Junio',
            '07-Julio',
            '08-Agosto',
            '09-Septiembre',
            '10-Octubre',
            '11-Noviembre',
            '12-Diciembre'
        ];

        $resultado = [];

        foreach ($meses as $mes) {
            $m = explode("-", $mes);

            if ($tipo) {
                if ($tipo == 'Bodega') {
                    $vencidos = $this->queryBodega($year, $m, $condicion);
                } else {
                    $vencidos = $this->queryPunto($year, $m, $condicion);
                }
            }

            $vencidos = InventarioNuevo::selectRaw('P.Nombre_Comercial, P.Nombre_General as Nombre, Inventario_Nuevo.Lote, Inventario_Nuevo.Fecha_Vencimiento, CONCAT_WS(" - ", B.Nombre, E.Nombre) as Bodega, (Inventario_Nuevo.Cantidad - Inventario_Nuevo.Cantidad_Apartada - Inventario_Nuevo.Cantidad_Seleccionada) as Cantidad, P.Referencia, P.Unidad_Empaque')
                ->join('Producto as P', 'P.Id_Producto', '=', 'Inventario_Nuevo.Id_Producto')
                ->join('Estiba as E', 'E.Id_Estiba', '=', 'Inventario_Nuevo.Id_Estiba')
                ->join('Punto_Dispensacion as B', 'B.Id_Punto_Dispensacion', '=', 'E.Id_Punto_Dispensacion')
                ->whereRaw("Inventario_Nuevo.Fecha_Vencimiento LIKE '%$year-" . $m[0] . "%'")
                ->whereRaw('(Inventario_Nuevo.Cantidad - Inventario_Nuevo.Cantidad_Apartada - Inventario_Nuevo.Cantidad_Seleccionada) > 0')
                ->orderBy('Inventario_Nuevo.Fecha_Vencimiento', 'ASC')
                ->get();




            $res = [
                "Mes" => $m[1],
                "Productos" => $vencidos,
            ];

            $resultado[] = $res;
        }

        return response()->json($resultado);

    }

    public function descargarExcel(Request $request)
    {
        $tipo = $request->input('tipo');
        $id_bodega_punto = $request->input('id_bodega_punto');
        $year = $request->input('year');

        $query = InventarioNuevo::select(
            'Producto.Nombre_Comercial',
            'Producto.Nombre_General',
            'Inventario_Nuevo.Lote',
            'Inventario_Nuevo.Fecha_Vencimiento',
            'Inventario_Nuevo.Costo',
            'Bodega_Nuevo.Nombre as Bodega',
            'Punto_Dispensacion.Nombre as Punto',
            DB::raw('(Inventario_Nuevo.Cantidad - Inventario_Nuevo.Cantidad_Apartada - Inventario_Nuevo.Cantidad_Seleccionada) as Cantidad')
        )
            ->join('Estiba', 'Estiba.Id_Estiba', '=', 'Inventario_Nuevo.Id_Estiba')
            ->join('Producto', 'Producto.Id_Producto', '=', 'Inventario_Nuevo.Id_Producto')
            ->leftJoin('Bodega_Nuevo', 'Bodega_Nuevo.Id_Bodega_Nuevo', '=', 'Estiba.Id_Bodega_Nuevo')
            ->leftJoin('Punto_Dispensacion', 'Punto_Dispensacion.Id_Punto_Dispensacion', '=', 'Estiba.Id_Punto_Dispensacion')
            ->where('Inventario_Nuevo.Fecha_Vencimiento', 'LIKE', '%' . $year . '%')
            ->whereRaw('(Inventario_Nuevo.Cantidad - Inventario_Nuevo.Cantidad_Apartada - Inventario_Nuevo.Cantidad_Seleccionada) > 0');

        if ($tipo) {
            if ($tipo == 'Bodega') {
                if (isset($id_bodega_punto) && $id_bodega_punto != 'todos') {
                    $query->where('Estiba.Id_Bodega_Nuevo', $id_bodega_punto);
                } else {
                    $query->where('Inventario_Nuevo.Id_Bodega', '!=', 0);
                }
            } else {
                if ($id_bodega_punto != 'todos') {
                    $query->where('Estiba.Id_Punto_Dispensacion', $id_bodega_punto);
                } else {
                    $query->where('Estiba.Id_Punto_Dispensacion', '!=', 0);
                }
            }
        }

        $vencidos = $query->orderBy('Inventario_Nuevo.Fecha_Vencimiento', 'ASC')->get();

        return Excel::download(new ReporteVencimientosExport($vencidos), 'reporte_vencimientos.xlsx');
    }

    public function queryPunto($year, $m, $condicion)
    {
        $vencidos = InventarioNuevo::selectRaw('P.Nombre_Comercial, P.Nombre_General, Inventario_Nuevo.Lote, Inventario_Nuevo.Fecha_Vencimiento, CONCAT_WS(" - ", B.Nombre, E.Nombre) as Bodega, (Inventario_Nuevo.Cantidad - Inventario_Nuevo.Cantidad_Apartada - Inventario_Nuevo.Cantidad_Seleccionada) as Cantidad')
            ->join('Producto as P', 'P.Id_Producto', '=', 'Inventario_Nuevo.Id_Producto')
            ->join('Estiba as E', 'E.Id_Estiba', '=', 'Inventario_Nuevo.Id_Estiba')
            ->join('Punto_Dispensacion as B', 'B.Id_Punto_Dispensacion', '=', 'E.Id_Punto_Dispensacion')
            ->whereRaw("Inventario_Nuevo.Fecha_Vencimiento LIKE '%$year-" . $m[0] . "%'")
            ->whereRaw('(Inventario_Nuevo.Cantidad - Inventario_Nuevo.Cantidad_Apartada - Inventario_Nuevo.Cantidad_Seleccionada) > 0')
            ->orderBy('Inventario_Nuevo.Fecha_Vencimiento', 'ASC')
            ->get();

        return $vencidos;
    }


    public function queryBodega($year, $m, $condicion)
    {
        $vencidos = InventarioNuevo::selectRaw('P.Nombre_Comercial, P.Nombre_General, Inventario_Nuevo.Lote, Inventario_Nuevo.Fecha_Vencimiento, CONCAT_WS(" - ", B.Nombre, E.Nombre) as Bodega, "" as Punto, (Inventario_Nuevo.Cantidad - Inventario_Nuevo.Cantidad_Apartada - Inventario_Nuevo.Cantidad_Seleccionada) as Cantidad')
            ->join('Producto as P', 'P.Id_Producto', '=', 'Inventario_Nuevo.Id_Producto')
            ->join('Estiba as E', 'E.Id_Estiba', '=', 'Inventario_Nuevo.Id_Estiba')
            ->join('Bodega_Nuevo as B', 'B.Id_Bodega_Nuevo', '=', 'E.Id_Bodega_Nuevo')
            ->whereRaw("Inventario_Nuevo.Fecha_Vencimiento LIKE '%$year-" . $m[0] . "%'")
            ->whereRaw('(Inventario_Nuevo.Cantidad - Inventario_Nuevo.Cantidad_Apartada - Inventario_Nuevo.Cantidad_Seleccionada) > 0')
            ->orderBy('Inventario_Nuevo.Fecha_Vencimiento', 'ASC')
            ->get();

        return $vencidos;
    }



    public function actaRecepcionCompraDevolucion()
    {
        $codigo = (isset($_REQUEST['codigo']) ? $_REQUEST['codigo'] : '');
        $tipoCompra = (isset($_REQUEST['compra']) ? $_REQUEST['compra'] : '');
        $id = (isset($_REQUEST['id']) ? $_REQUEST['id'] : '');

        /* $oItem = new complex('Orden_Compra_'.$tipoCompra, 'Codigo', $codigo, 'Varchar');
        $attr = 'Id_Orden_Compra_' . $tipoCompra;
        $id_compra = $oItem->$attr; */
        $query = '';
        switch ($tipoCompra) {

            case "Nacional": {
                $query = 'SELECT P.Nombre_General as Nombre_Producto,
        PNC.Cantidad as CantidadProducto, CN.Nombre as NombreCategoria, SC.Nombre as NombreSubcategoria,
      (SELECT POC.Total FROM Producto_Orden_Compra_Nacional POC WHERE POC.Id_Producto=PNC.Id_Producto AND POC.Id_Orden_Compra_Nacional=PNC.Id_Compra LIMIT 1 ) as CostoProducto,
         (SELECT POC.Id_Producto_Orden_Compra_Nacional FROM Producto_Orden_Compra_Nacional POC WHERE POC.Id_Producto=PNC.Id_Producto AND POC.Id_Orden_Compra_Nacional=PNC.Id_Compra LIMIT 1 ) AS Id_Producto_Orden_Compra,
        P.Nombre_Comercial,
        P.Id_Producto as Id_Producto,
        IF(P.Gravado="Si",19,0) AS Impuesto,
        P.Imagen AS Foto,
        P.Id_Categoria,
        IF(P.Codigo_Barras IS NULL, "No", "Si") AS Codigo_Barras,
        0 as Cantidad,
        0 as Cantidad_Band,
        0 as Precio,
        0 as Subtotal,
        0 as Iva,
        "" as Lote,
        "" as Fecha_Vencimiento,
        0 as No_Conforme,
        false as Checkeado,
        true AS Required,
        "No"as Eliminado
   FROM
    Producto_No_Conforme PNC
       INNER JOIN Producto P
        ON P.Id_Producto = PNC.Id_Producto
        INNER JOIN Categoria_Nueva CN
        ON P.Id_Categoria = CN.Id_Categoria_Nueva
        LEFT JOIN Subcategoria SC
        ON P.Id_Subcategoria = SC.Id_Subcategoria
   WHERE PNC.Id_No_Conforme=' . $id;

                $query1 = "SELECT 'Nacional' AS Tipo, Id_Orden_Compra_Nacional AS Id_Orden_Compra, Codigo, Identificacion_Funcionario, Id_Bodega_Nuevo, (SELECT IFNULL(P.social_reason, CONCAT_WS(' ', P.first_name, P.first_surname)) FROM third_parties P WHERE P.id=OCN.Id_Proveedor) AS Proveedor, OCN.Id_Proveedor FROM Orden_Compra_Nacional OCN WHERE Codigo = '" . $codigo . "'";

                break;
            }

        }

        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->setTipo('Multiple');
        $res = $oCon->getData();
        unset($oCon);


        $oCon = new consulta();
        $oCon->setQuery($query1);
        $res1 = $oCon->getData();
        unset($oCon);

        $resultado['encabezado'] = $res1;
        $resultado['producto'] = $res;
        // return $res;

        $i = -1;
        foreach ($res as $value) {
            $i++;
            // return $resultado['producto'][$i];
            $resultado['producto'][$i]->producto[] = (array) $value;
            $product = ProductoOrdenCompraNacional::find($resultado['producto'][$i]->Id_Producto_Orden_Compra);
            $product->variables = collect();
            if ($product->product->category && $product->product->subcategory) {
                $product->variables = $product->product->category->categoryVariables
                    ->merge($product->product->subcategory->subcategoryVariables)
                    ->where('reception', true);
            }
            $resultado['producto'][$i]->variables = $product->variables;
        }

        //         $query_retenciones = '
//   SELECT
//     P.Tipo_Retencion,
//     P.Id_Plan_Cuenta_Retefuente,
//     (IF(P.Id_Plan_Cuenta_Retefuente IS NULL OR P.Id_Plan_Cuenta_Retefuente = 0, "", (SELECT Nombre FROM Plan_Cuentas WHERE Id_Plan_Cuentas = P.Id_Plan_Cuenta_Retefuente))) AS Nombre_Retefuente,
//     (IF(P.Id_Plan_Cuenta_Retefuente IS NULL OR P.Id_Plan_Cuenta_Retefuente = 0, "0", (SELECT Porcentaje FROM Retencion WHERE Id_Plan_Cuenta = P.Id_Plan_Cuenta_Retefuente))) AS Porcentaje_Retefuente,
//     (IF(P.Id_Plan_Cuenta_Retefuente IS NULL OR P.Id_Plan_Cuenta_Retefuente = 0, "0", (SELECT Id_Retencion FROM Retencion WHERE Id_Plan_Cuenta = P.Id_Plan_Cuenta_Retefuente))) AS Id_Retencion_Fte,
//     P.Tipo_Reteica,
//     P.Id_Plan_Cuenta_Reteica,
//     (IF(P.Id_Plan_Cuenta_Reteica IS NULL OR P.Id_Plan_Cuenta_Reteica = 0, "", (SELECT Nombre FROM Plan_Cuentas WHERE Id_Plan_Cuentas = P.Id_Plan_Cuenta_Reteica))) AS Nombre_Reteica,
//     (IF(P.Id_Plan_Cuenta_Reteica IS NULL OR P.Id_Plan_Cuenta_Reteica = 0, "0", (SELECT Porcentaje FROM Retencion WHERE Id_Plan_Cuenta = P.Id_Plan_Cuenta_Reteica))) AS Porcentaje_Reteica,
//     (IF(P.Id_Plan_Cuenta_Retefuente IS NULL OR P.Id_Plan_Cuenta_Retefuente = 0, "0", (SELECT Id_Retencion FROM Retencion WHERE Id_Plan_Cuenta = P.Id_Plan_Cuenta_Reteica))) AS Id_Retencion_Ica,
//     P.Contribuyente,
//     P.Id_Plan_Cuenta_Reteiva,
//     (IF(P.Id_Plan_Cuenta_Reteiva IS NULL OR P.Id_Plan_Cuenta_Reteiva = 0, "", (SELECT Nombre FROM Plan_Cuentas WHERE Id_Plan_Cuentas = P.Id_Plan_Cuenta_Reteiva))) AS Nombre_Reteiva,
//     (IF(P.Id_Plan_Cuenta_Reteiva IS NULL OR P.Id_Plan_Cuenta_Reteiva = 0, "0", (SELECT Porcentaje FROM Retencion WHERE Id_Plan_Cuenta = P.Id_Plan_Cuenta_Reteiva))) AS Porcentaje_Reteiva,
//     (IF(P.Id_Plan_Cuenta_Retefuente IS NULL OR P.Id_Plan_Cuenta_Retefuente = 0, "0", (SELECT Id_Retencion FROM Retencion WHERE Id_Plan_Cuenta = P.Id_Plan_Cuenta_Reteiva))) AS Id_Retencion_Iva,
//     Regimen
//   FROM Proveedor P
//   WHERE
//     Id_Proveedor = ' . $res1['Id_Proveedor'];

        //         $oCon = new consulta();
//         $oCon->setQuery($query_retenciones);
//         $retenciones_proveedor = $oCon->getData();
//         unset($oCon);

        // $resultado['Data_Retenciones'] = $retenciones_proveedor;
        $resultado['Data_Retenciones'] = [];

        //         $query_configuracion = '
//   SELECT
//     Valor_Unidad_Tributaria,
//     Base_Retencion_Compras_Reg_Comun,
//     Base_Retencion_Compras_Reg_Simpl,
//     Base_Retencion_Compras_Ica,
//     Base_Retencion_Iva_Reg_Comun
//   FROM Configuracion
//   WHERE
//       Id_Configuracion = 1';

        //         $oCon = new consulta();
//         $oCon->setQuery($query_configuracion);
//         $valores_retenciones = $oCon->getData();
//         unset($oCon);

        //         $resultado['Valores_Base_Retenciones'] = $valores_retenciones;
        $resultado['Valores_Base_Retenciones'] = [];

        return $this->success($resultado);
    }

    public function guardarActaRecepcionDevolucion()
    {
        $contabilizar = new Contabilizar();
        // $configuracion = new Configuracion();

        $datos = (isset($_REQUEST['datos']) ? $_REQUEST['datos'] : '');
        $productoCompra = (isset($_REQUEST['productos']) ? $_REQUEST['productos'] : '');
        $codigoCompra = (isset($_REQUEST['codigoCompra']) ? $_REQUEST['codigoCompra'] : '');
        $tipoCompra = (isset($_REQUEST['tipoCompra']) ? $_REQUEST['tipoCompra'] : '');
        $facturas = (isset($_REQUEST['facturas']) ? $_REQUEST['facturas'] : '');
        $productos_eliminados = (isset($_REQUEST['eliminados']) ? $_REQUEST['eliminados'] : '');
        $archivos = (isset($_REQUEST['archivos']) ? $_REQUEST['archivos'] : '');
        $no_conforme_devolucion = (isset($_REQUEST['id_no_conforme']) ? $_REQUEST['id_no_conforme'] : false);

        $datosProductos = (array) json_decode($productoCompra, true);
        $datos = (array) json_decode($datos);
        $facturas = (array) json_decode($facturas, true);
        $productos_eliminados = (array) json_decode($productos_eliminados, true);

        $datos_movimiento_contable = array();

        $cod = generateConsecutive('Acta_Recepcion');
        sumConsecutive('Acta_Recepcion');
        // $cod = $configuracion->getConsecutivo('Acta_Recepcion', 'Acta_Recepcion');
        $datos['Codigo'] = $cod;
        $oItem = new complex("Acta_Recepcion", "Id_Acta_Recepcion");
        foreach ($datos as $index => $value) {
            $oItem->$index = $value;
        }
        if ($datos['Tipo'] == 'Nacional') {
            $oItem->Id_Orden_Compra_Nacional = $datos['Id_Orden_Compra'];
        } else {
            $oItem->Id_Orden_Compra_Internacional = $datos['Id_Orden_Compra'];
        }

        $oItem->save();
        $id_Acta_Recepcion = $oItem->getId();
        unset($oItem);

        /* AQUI GENERA QR */
        $qr = generarqr('actarecepcion', $id_Acta_Recepcion, '/IMAGENES/QR/');
        $oItem = new complex("Acta_Recepcion", "Id_Acta_Recepcion", $id_Acta_Recepcion);
        $oItem->Codigo_Qr = $qr;
        $oItem->save();
        unset($oItem);
        /* HASTA AQUI GENERA QR */



        // productos
        $oCon = new consulta();

        $i = 0;
        foreach ($datosProductos as $prod) {
            unset($prod["producto"][count($prod["producto"]) - 1]);
            foreach ($prod["producto"] as $item) {
                $i++;

                if ($item['Lote'] != '' && $prod['Eliminado'] == 'No') {

                    $oItem = new complex('Producto_Acta_Recepcion', 'Id_Producto_Acta_Recepcion');
                    //mandar productos a Producto_Acta_Recepcion
                    foreach ($item as $index => $value) {
                        $oItem->$index = $value;
                    }
                    $subtotal = ((INT) $item['Cantidad']) * ((INT) $item['Precio']);
                    $oItem->Id_Producto_Orden_Compra = $prod["Id_Producto_Orden_Compra"];
                    $oItem->Codigo_Compra = $codigoCompra;
                    $oItem->Tipo_Compra = $tipoCompra;
                    $oItem->Id_Acta_Recepcion = $id_Acta_Recepcion;
                    // $precio = number_format($prod['Precio'],2,".","");
                    $subtotal = number_format((INT) $subtotal, 2, ".", "");
                    $oItem->Subtotal = $subtotal;
                    $oItem->save();
                    unset($oItem);
                }



                // ACA SE AGREGA EL PRODUCTO A LA LISTA DE GANANCIA
                $query = 'SELECT LG.Porcentaje, LG.Id_Lista_Ganancia FROM Lista_Ganancia LG';
                $oCon = new consulta();
                $oCon->setQuery($query);
                $oCon->setTipo('Multiple');
                $porcentaje = $oCon->getData();
                unset($oCon);
                //datos
                $cum_producto = $this->GetCodigoCum($item['Id_Producto']);
                foreach ($porcentaje as $value) {
                    $query = 'SELECT * FROM Producto_Lista_Ganancia WHERE Cum="' . $cum_producto . '" AND Id_lista_Ganancia=' . $value['Id_Lista_Ganancia'];
                    $oCon = new consulta();
                    $oCon->setQuery($query);
                    $cum = $oCon->getData();
                    unset($oCon);
                    if ($cum) {
                        $precio = number_format($item['Precio'] / ((100 - $value['Porcentaje']) / 100), 2, '.', '');
                        if ($cum['Precio'] < $precio) {
                            $oItem = new complex('Producto_Lista_Ganancia', 'Id_Producto_Lista_Ganancia', $cum['Id_Producto_Lista_Ganancia']);

                            $oItem->Precio = $precio;
                            $oItem->Id_Lista_Ganancia = $value['Id_Lista_Ganancia'];
                            $oItem->save();
                            unset($oItem);
                        }

                    } else {
                        $oItem = new complex('Producto_Lista_Ganancia', 'Id_Producto_Lista_Ganancia');
                        $oItem->Cum = $cum_producto;
                        $precio = number_format($item['Precio'] / ((100 - $value['Porcentaje']) / 100), 2, '.', '');
                        $oItem->Precio = $precio;
                        $oItem->Id_Lista_Ganancia = $value['Id_Lista_Ganancia'];
                        $oItem->save();
                        unset($oItem);
                    }
                }
            }

        }

        // Agregando facturas
        $i = -1;
        if ($facturas[count($facturas) - 1]["Factura"] == "") {
            unset($facturas[count($facturas) - 1]);
        }
        foreach ($facturas as $fact) {
            $i++;
            $oItem = new complex('Factura_Acta_Recepcion', 'Id_Factura_Acta_Recepcion');
            $oItem->Id_Acta_Recepcion = $id_Acta_Recepcion;
            $oItem->Factura = $fact["Factura"];
            $oItem->Fecha_Factura = $fact["Fecha_Factura"];

            if (!empty($_FILES["archivos$i"]['name'])) {
                $posicion1 = strrpos($_FILES["archivos$i"]['name'], '.') + 1;
                $extension1 = substr($_FILES["archivos$i"]['name'], $posicion1);
                $extension1 = strtolower($extension1);
                $_filename1 = uniqid() . "." . $extension1;
                $_file1 = "/home/ateneoerp/inventario.ateneoerp.com/" . "ARCHIVOS/FACTURAS_COMPRA/" . $_filename1;

                // $subido1 = move_uploaded_file($_FILES["archivos$i"]['tmp_name'], $_file1);
                $subido1 = saveBase64File($_file1, false, '.pdf');
                if ($subido1) {
                    @chmod($_file1, 0777);
                    $oItem->Archivo_Factura = $_filename1;
                }
            }
            $oItem->Id_Orden_Compra = $datos['Id_Orden_Compra'];
            $oItem->Tipo_Compra = $datos['Tipo'];
            $oItem->save();
            $id_factura = $oItem->getId();
            unset($oItem);

            if (count($fact['Retenciones']) > 0) {

                foreach ($fact['Retenciones'] as $rt) {

                    // $oItem = new complex("Factura_Acta_Recepcion_Retencion","Id_Factura_Acta_Recepcion_Retencion");
                    // $oItem->Id_Factura = $id_factura;
                    // $oItem->Id_Retencion =$rt['Id_Retencion'];
                    // $oItem->Id_Acta_Recepcion = $id_Acta_Recepcion;
                    // $oItem->Valor_Retencion = number_format(floatval($rt['Valor']),2,".","");
                    // $oItem->save();
                    // unset($oItem);

                    if ($rt['Valor'] > 0) {
                        $oItem = new complex("Factura_Acta_Recepcion_Retencion", "Id_Factura_Acta_Recepcion_Retencion");
                        $oItem->Id_Factura = $id_factura;
                        $oItem->Id_Retencion = $rt['Id_Retencion'];
                        $oItem->Id_Acta_Recepcion = $id_Acta_Recepcion;
                        $oItem->Valor_Retencion = $rt['Valor'] != 0 ? round($rt['Valor'], 0) : '0';
                        $oItem->save();
                        unset($oItem);
                    }
                }
            }
        }

        // Actualizando datos del producto
        $h = -1;
        foreach ($datosProductos as $value) {
            $h++;
            $oItem = new complex('Producto', 'Id_Producto', $value["Id_Producto"]);

            if ($oItem->Id_Categoria != $value["Id_Categoria"]) {
                $oItem->Id_Categoria = $value["Id_Categoria"];
            }
            if ($oItem->Peso_Presentacion_Regular != $value["Peso"]) {
                $oItem->Peso_Presentacion_Regular = $value["Peso"];
            }
            if (isset($_FILES["fotos$h"]['name'])) {
                $posicion2 = strrpos($_FILES["fotos$h"]['name'], '.') + 1;
                $extension2 = substr($_FILES["fotos$h"]['name'], $posicion2);
                $extension2 = strtolower($extension2);
                $_filename2 = uniqid() . "." . $extension2;
                $_file2 = "/home/ateneoerp/inventario.ateneoerp.com/" . "IMAGENES/PRODUCTOS/" . $_filename2;

                $subido2 = move_uploaded_file($_FILES["fotos$h"]['tmp_name'], $_file2);
                if ($subido2) {
                    @chmod($_file2, 0777);
                    $oItem->Imagen = $_filename2;
                }
            }
            $oItem->save();
            unset($oItem);
        }

        $contador = 0;
        if ($contador == 0) {
            $resultado['mensaje'] = "Se ha guardado correctamente el acta de recepcion";
            $resultado['tipo'] = "success";
        } else {
            $resultado['mensaje'] = "Se ha guardado correctamente el acta de recepcion con los productos No Conformes";
            $resultado['tipo'] = "success";
        }

        //Consultar el codigo del acta y el id de la orden de compra
        $query_codido_acta = 'SELECT
                        Codigo,
                        Id_Orden_Compra_Nacional
                    FROM
                        Acta_Recepcion
                    WHERE
                        Id_Acta_Recepcion = ' . $id_Acta_Recepcion;

        $oCon = new consulta();
        $oCon->setQuery($query_codido_acta);
        $acta_data = $oCon->getData();
        unset($oCon);

        //Guardando paso en el seguimiento del acta en cuestion
        $oItem = new complex('Actividad_Orden_Compra', "Id_Acta_Recepcion_Compra");
        $oItem->Id_Orden_Compra_Nacional = $acta_data['Id_Orden_Compra_Nacional'];
        $oItem->Id_Acta_Recepcion_Compra = $id_Acta_Recepcion;
        $oItem->Identificacion_Funcionario = Person::find(Auth()->user()->person_id)->id;
        $oItem->Detalles = "Se recibio el acta con codigo " . $acta_data['Codigo'] . " de los productos faltantes de la orden de compra";
        $oItem->Fecha = date("Y-m-d H:i:s");
        $oItem->Estado = 'Recepcion';
        $oItem->save();
        unset($oItem);

        //GUARDAR MOVMIMIENTO CONTABLE ACTA*/
        $datos_movimiento_contable['Id_Registro'] = $id_Acta_Recepcion;
        $datos_movimiento_contable['Numero_Comprobante'] = $cod;
        $datos_movimiento_contable['Nit'] = $datos['Id_Proveedor'];
        $datos_movimiento_contable['Productos'] = $datosProductos;
        $datos_movimiento_contable['Facturas'] = $facturas;

        $contabilizar->CrearMovimientoContable('Acta Recepcion', $datos_movimiento_contable);

        if (count($productos_eliminados) > 0) {
            $productos_eliminados = implode(',', $productos_eliminados);
            $this->EliminarNoConformes($no_conforme_devolucion, $productos_eliminados);
        }


        // echo json_encode($resultado);

        return $this->success($resultado);

        //$oitem = new Complex("Producto_Acta_Recepcion" , "Id_Producto_Acta_Recepcion");


    }

    private function GetCodigoCum($id_producto)
    {


        $query = '
        SELECT
            Codigo_Cum
        FROM Producto
        WHERE
            Id_Producto = ' . $id_producto;

        $oCon = new consulta();
        $oCon->setQuery($query);
        $cum = $oCon->getData();
        unset($oCon);


        return $cum['Codigo_Cum'];
    }

    private function EliminarNoConformes($id, $productos)
    {
        $query = "DELETE FROM Producto_No_Conforme WHERE Id_No_Conforme=$id AND Id_Producto NOT IN ($productos)";
        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->createData();
        unset($oCon);

    }

    public function actaRecepcionRemision()
    {
        $id_remision = (isset($_REQUEST['id']) ? $_REQUEST['id'] : '');


        $query = 'SELECT R.*, R.Nombre_Origen  as Nombre_Bodega,
        (CASE
        WHEN  R.Tipo_Bodega="REFRIGERADOS" THEN "Si"
        ELSE "No"
        END) as Temperatura
        FROM Remision R
        WHERE R.Id_Remision=' . $id_remision;

        $oCon = new consulta();
        $oCon->setQuery($query);
        $remision = $oCon->getData();
        unset($oCon);

        $query = $this->ObtenerQuery($remision['Entrega_Pendientes'], $id_remision);
        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->setTipo('Multiple');
        $productos = $oCon->getData();

        foreach ($productos as $i => $prod) {
            $lote = ['Lote' => $prod->Lote, 'Fecha_Vencimiento' => $prod->Fecha_Vencimiento, 'Cantidad' => 0, 'Cantidad_Ingresada' => 0];
            $prod->Lotes[] = $lote;

            $productos[$i] = $prod;
        }
        unset($oCon);

        $productos_Pendientes = $this->ObtenerProductosPendientes($remision['Entrega_Pendientes'], $id_remision);


        $resultado["Datos"] = $remision;
        $resultado["Productos"] = $productos;
        if (count($productos_Pendientes) > 0) {
            $resultado["Productos_Pendientes"] = $productos_Pendientes;
        } else {
            $resultado["Productos_Pendientes"] = [];
        }

        return $this->success($resultado);
    }

    private function ObtenerQuery($tipo, $id_remision)
    {
        $query = '';
        if ($tipo == 'Si') {
            $query = 'SELECT PR.*, P.Nombre_Comercial, P.Nombre_General as Nombre_Producto, 0 as Seleccionado, "Si" as Cumple, "Si" as Revisado, "1" AS Id_Causal_No_Conforme, (PR.Cantidad - IFNULL((SELECT SUM(PD.Cantidad) FROM Producto_Descarga_Pendiente_Remision PD WHERE PD.Id_Producto_Remision = PR.Id_Producto_Remision), 0)) as Cantidad, "" as Temperatura
            FROM Producto_Remision PR
            INNER JOIN Producto P ON PR.Id_Producto = P.Id_Producto
            WHERE PR.Id_Remision = ' . $id_remision . ' HAVING Cantidad > 0 ';
        } else {
            $query = 'SELECT PR.*, P.Nombre_Comercial, P.Nombre_General as Nombre_Producto, 0 as Seleccionado, "Si" as Cumple, "Si" as Revisado, "1" AS Id_Causal_No_Conforme
            FROM Producto_Remision PR
            INNER JOIN Producto P ON PR.Id_Producto = P.Id_Producto
            WHERE PR.Id_Remision = ' . $id_remision;
        }

        return $query;
    }


    private function ObtenerProductosPendientes($tipo, $id_remision)
    {
        if ($tipo == 'Si') {
            $query = 'SELECT PR.Lote, P.Nombre_Comercial, P.Nombre_General AS Nombre_Producto, PR.Cantidad, 
        (SELECT CONCAT(Pa.Id_Paciente," - ",Pa.Primer_Nombre," ",Pa.Primer_Apellido," ",Pa.Segundo_Apellido) FROM Paciente Pa WHERE Pa.Id_Paciente = PR.Id_Paciente) as Paciente, 
        (SELECT D.Codigo FROM Dispensacion D WHERE D.Id_Dispensacion = PR.Id_Dispensacion) as DIS
        FROM Producto_Descarga_Pendiente_Remision PR
        INNER JOIN Producto P ON PR.Id_Producto = P.Id_Producto
        WHERE PR.Id_Remision = ' . $id_remision . ' ORDER BY P.Nombre_Comercial';

            $oCon = new consulta();
            $oCon->setQuery($query);
            $oCon->setTipo('Multiple');
            $productos_pendientes = $oCon->getData();
            unset($oCon);

        } else {
            $productos_pendientes = [];
        }
        return $productos_pendientes;
    }


    public function listaRemisionesPendientes(Request $request)
    {
        // Obtener el id del punto de dispensación del usuario autenticado
        $id_punto_dispensacion = Person::find(auth()->user()->person_id)->dispensing_point_id;

        $cod = $request->input('cod', '');

        $remisiones = Remision::select('Remision.Codigo','Remision.Fecha','Remision.Tipo_Bodega', 'Remision.Id_Origen', 'Remision.Id_Remision', 'people.image as Imagen', DB::raw('(SELECT COUNT(*) FROM Producto_Remision WHERE Producto_Remision.Id_Remision = Remision.Id_Remision) as Items'), 'Remision.Orden_Compra as Id_Orden_Compra_Nacional')
            ->join('people', 'Remision.Identificacion_Funcionario', '=', 'people.id')
            ->where('Remision.Estado_Alistamiento', 2)
            ->where('Remision.Tipo_Destino', 'Punto_Dispensacion')
            ->where('Remision.Estado', 'Enviada')
            ->where('Remision.Codigo', $cod)
            ->where('Remision.Id_Destino', $id_punto_dispensacion);

        $ordenesCompra = PurchaseRequest::select('purchase_requests.code as Codigo', 'purchase_requests.created_at as Fecha',  DB::raw('NULL as Tipo_Bodega'),  DB::raw('NULL as Id_Origen'),  DB::raw('NULL as Id_Remision'), 'people.image as Imagen','purchase_requests.quantity_of_products as Items', 'Orden_Compra_Nacional.Id_Orden_Compra_Nacional')
            ->join('orden_compra_nacional_purchase_request', 'orden_compra_nacional_purchase_request.id_purchase_request', '=', 'purchase_requests.id')
            ->join('Orden_Compra_Nacional', 'orden_compra_nacional_purchase_request.id_orden_compra', '=', 'Orden_Compra_Nacional.Id_Orden_Compra_Nacional')
            ->join('people', 'purchase_requests.user_id', '=', 'people.id')
            ->where('purchase_requests.dispensation_point_id', $id_punto_dispensacion)
            ->where('purchase_requests.code', $cod)
            ->where('Orden_Compra_Nacional.Aprobacion', 'Aprobada')
            ->whereNotIn('Orden_Compra_Nacional.Estado', ['Recibida', 'Anulada'])
            ->where('Orden_Compra_Nacional.Id_Bodega_Nuevo', '<>', 0);

        // Combinar las dos consultas
        $resultado = $remisiones->union($ordenesCompra)->get();

        return $this->success($resultado);
    }

    public function detalleActaRecepcionRemision()
    {
        $id_acta = (isset($_REQUEST['id']) ? $_REQUEST['id'] : '');

        $query = 'SELECT AR.*, P.Nombre as Nombre_Punto, R.Codigo as Codigo_Remision, R.Nombre_Origen, R.Entrega_Pendientes
        FROM Acta_Recepcion_Remision AR
        INNER JOIN Punto_Dispensacion P
        ON AR.Id_Punto_Dispensacion=P.Id_Punto_Dispensacion
        INNER JOIN Remision R
        ON AR.ID_Remision=R.Id_Remision
        WHERE AR.Id_Acta_Recepcion_Remision=' . $id_acta;

        $oCon = new consulta();
        $oCon->setQuery($query);
        $datos = $oCon->getData();
        unset($oCon);
        $query2 = $this->ObtenerQuery2($datos['Entrega_Pendientes'], $id_acta);

        $oCon = new consulta();
        $oCon->setTipo('Multiple');
        $oCon->setQuery($query2);
        $productos_acta = $oCon->getData();
        unset($oCon);

        $resultado = [];

        $resultado["Datos"] = $datos;
        $resultado["Productos"] = $productos_acta;

        return $this->success($resultado);
    }

    private function ObtenerQuery2($tipo, $id)
    {
        $query = '';
        if ($tipo == 'Si') {
            $query = 'SELECT PR.*, P.Nombre_Comercial, P.Nombre_General as Nombre_Producto, (PR.Cantidad - IFNULL((SELECT SUM(PD.Cantidad) FROM Producto_Descarga_Pendiente_Remision PD WHERE PD.Id_Producto_Remision = PR.Id_Producto_Remision), 0)) as Cantidad, IFNULL((SELECT SUM(PD.Cantidad) FROM Producto_Descarga_Pendiente_Remision PD WHERE PD.Id_Producto_Remision = PR.Id_Producto_Remision), 0) as Cantidad_Disp
            FROM Producto_Acta_Recepcion_Remision PR
            INNER JOIN Producto P ON PR.Id_Producto = P.Id_Producto
            WHERE PR.Id_Acta_Recepcion_Remision = ' . $id;
        } else {
            $query = 'SELECT P.*, P.Nombre_Comercial, P.Nombre_General as Nombre_Producto
           FROM Producto_Acta_Recepcion_Remision P
           INNER JOIN Producto PRD ON P.Id_Producto = PRD.Id_Producto
           WHERE P.Id_Acta_Recepcion_Remision = ' . $id;
        }

        return $query;
    }

    public function getEstibas()
    {
        $bodega = isset($_REQUEST['Id_Bodega_Nuevo']) ? $_REQUEST['Id_Bodega_Nuevo'] : null;
        $grupo = isset($_REQUEST['Id_Grupo_Estiba']) ? $_REQUEST['Id_Grupo_Estiba'] : null;
        $filtros = isset($_REQUEST['Filtros']) ? json_decode($_REQUEST['Filtros'], true) : [];
        $currentPage = isset($_REQUEST['currentPage']) ? (int) $_REQUEST['currentPage'] : 1;
        $limitPage = isset($_REQUEST['limit']) ? (int) $_REQUEST['limit'] : 10;

        $query = Estiba::query();

        // Aplicar condiciones dinámicas
        if ($bodega) {
            $query->where('Id_Bodega_Nuevo', $bodega);
        }

        if ($grupo) {
            $query->where('Id_Grupo_Estiba', $grupo);
        }

        if (!empty($filtros['Nombre'])) {
            $query->where('Nombre', 'LIKE', '%' . $filtros['Nombre'] . '%');
        }

        if (!empty($filtros['Codigo_Barras'])) {
            $query->where('Codigo_Barras', 'LIKE', '%' . $filtros['Codigo_Barras'] . '%');
        }

        if (!empty($filtros['Estado'])) {
            $query->where('Estado', $filtros['Estado']);
        }

        // Aplicar paginación
        $resultado = $query->selectRaw('*, Id_Estiba AS value, Nombre AS label')
        ->get();


        return $this->success($resultado);
    }

}
