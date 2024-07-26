<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use App\Http\Services\consulta;
use App\Http\Services\QueryBaseDatos;
use App\Http\Services\complex;
use App\Http\Services\CostoPromedio;
use App\Http\Services\Contabilizar;
use App\Http\Services\Configuracion;
use App\Http\Services\HttpResponse;
use App\Http\Services\NumeroALetras;
use App\Models\ActaRecepcion;
use App\Models\ActaRecepcionRemision;
use App\Models\ActividadOrdenCompra;
use App\Models\AjusteIndividual;
use App\Models\BodegaNuevo;
use App\Models\CausalAnulacion;
use App\Models\CausalNoConforme;
use App\Models\CompanyConfiguration;
use App\Models\FacturaActaRecepcion;
use App\Models\FuncionarioBodegaNuevo;
use App\Models\Impuesto;
use App\Models\NacionalizacionParcial;
use App\Models\NoConforme;
use App\Models\NotaCredito;
use App\Models\OrdenCompraNacional;
use App\Models\People;
use App\Models\Person;
use App\Models\ProductoActaRecepcion;
use App\Models\ProductoNoConforme;
use App\Models\Subcategory;
use App\Models\VariableProduct;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class ActaRecepcionController extends Controller
{
    use ApiResponser;

    public function listaImpuestoMes()
    {
        $impuestos = Impuesto::all();
        $meses = CompanyConfiguration::pluck('Expiration_Months');
        $bodegas = BodegaNuevo::select('Id_Bodega_Nuevo', 'Nombre')->get();

        $resultado = [
            'Impuesto' => $impuestos,
            'Meses' => $meses,
            'Bodega' => $bodegas,
        ];

        return response()->json($resultado);
    }

    public function listaSubcategorias()
    {
        $idBodega = request()->input('id_bodega', false);

        $query = Subcategory::select('Subcategoria.*', 'Categoria_Nueva.Nombre AS Categoria_Nueva')
            ->join('Categoria_Nueva', 'Subcategoria.Id_Categoria_Nueva', '=', 'Categoria_Nueva.Id_Categoria_Nueva')
            ->orderBy('Categoria_Nueva.Id_Categoria_Nueva')
            ->get();

        $resultado = $this->separarPorEstiba($query);

        return response()->json($resultado);
    }

    public function validateActa($id)
    {
        // Obtener el acta de recepción con todas las relaciones necesarias
        $actaRecepcion = ActaRecepcion::with(
            'facturas',
            'products.variables',
            'products.causalNoConforme',
            'products.product.category.categoryVariables',
            'products.product.subcategory.subcategoryVariables'
        )->where('Id_Orden_Compra_Nacional', $id)->first();

        if (!$actaRecepcion) {
            return $this->success($actaRecepcion);
        }

        $groupedProducts = $actaRecepcion->products->groupBy('Id_Producto');

        $actaRecepcion->grouped_products = $groupedProducts;

        unset($actaRecepcion->products);

        return $this->success($actaRecepcion);
    }

    function separarPorEstiba($resultado)
    {
        $porCategorias = [];
        $porCategorias[0]['Categoria_Nueva'] = $resultado[0]->Categoria_Nueva;
        $porCategorias[0]['Subcategorias'] = [];

        $XEstiba = 0; //index por Estiba
        $XProducto = 0; //index Por Producto

        foreach ($resultado as $key => $categorias) {

            if ($porCategorias[$XEstiba]['Categoria_Nueva'] == $categorias->Categoria_Nueva) {
                $porCategorias[$XEstiba]['Subcategorias'][$XProducto]['Nombre_Subcategoria'] = $categorias->Nombre;
                $porCategorias[$XEstiba]['Subcategorias'][$XProducto]['Id_Subcategoria'] = $categorias->Id_Subcategoria;
                $XProducto++;
            } else {
                $XEstiba++;
                $XProducto = 0;
                $porCategorias[$XEstiba]['Categoria_Nueva'] = $categorias->Categoria_Nueva;
                $porCategorias[$XEstiba]['Subcategorias'][$XProducto]['Nombre_Subcategoria'] = $categorias->Nombre;

                $porCategorias[$XEstiba]['Subcategorias'][$XProducto]['Id_Subcategoria'] = $categorias->Id_Subcategoria;
                $XProducto++;
            }
        }
        return $porCategorias;
    }

    public function listarPendientes()
    {
        $actaRecepcion = ActaRecepcion::select(
            '*',
            DB::raw('( SELECT GROUP_CONCAT(Factura) FROM Factura_Acta_Recepcion WHERE Id_Acta_Recepcion = Acta_Recepcion.Id_Acta_Recepcion ) as Facturas')
        )
            ->with('person')
            ->with([
                'orden' => function ($q) {
                    $q->where('Id_Bodega_Nuevo', '<>', null);
                }
            ])
            ->with('bodega:Id_Bodega_Nuevo,Nombre')
            ->where('Acta_Recepcion.Estado', '=', 'Creada')
            ->orderBy('Fecha_Creacion', 'desc')
            ->orderBy('Codigo', 'desc')
            ->paginate(Request()->get('pageSize', 10), ['*'], 'page', Request()->get('page', 1));

        return $this->success($actaRecepcion);
    }

    public function listarAnuladas(Request $request)
    {
        return $this->success(
            ActaRecepcion::with('orden', 'causal', 'third')
                ->where('Estado', 'Anulada')
                ->when($request->input('tipo_compra'), function ($query, $tipo_compra) {
                    $query->where('tipo_compra', $tipo_compra);
                })
                ->when($request->input('codigo'), function ($query, $codigo) {
                    $query->where('Codigo', 'like', "%$codigo%");
                })
                ->paginate(
                    $request->input('tam', 10),
                    ['*'],
                    'page',
                    $request->input('pag', 1)
                )
        );
    }

    public function indexCausalAnulacion()
    {
        return $this->success(CausalAnulacion::orderBy('Nombre')->get());
    }

    public function listarActas(Request $request)
    {
        $idFuncionario = $request->input('id_funcionario');
        $tipo = $request->input('tipo', '');
        $estado = $request->input('estado', '');
        $cod = $request->input('cod', '');
        $compra = $request->input('compra', '');
        $proveedor = $request->input('proveedor', '');
        $fecha = $request->input('fecha', '');
        $fecha2 = $request->input('fecha2', '');
        $fact = $request->input('fact', '');

        $enbodega = '';
        if ($tipo !== "General") {
            $bodegasFuncionario = FuncionarioBodegaNuevo::where('Identificacion_Funcionario', $idFuncionario)
                ->pluck('Id_Bodega_Nuevo')
                ->toArray();
            $enbodega = ' AND (AR.Id_Bodega_Nuevo IN(' . implode(',', $bodegasFuncionario) . ') OR AR.Id_Bodega_Nuevo = 0) ';
        }

        $estadoCond = '';
        if ($estado === 'Acomodada') {
            $estadoCond = ' (AR.Estado ="' . $estado . '" OR AR.Fecha_Creacion < "2020-07-22") ';
        } else if ($estado === 'Aprobada') {
            $estadoCond = ' AR.Estado ="' . $estado . '" AND AR.Fecha_Creacion > "2020-07-22" ';
        }

        $condicion = '';
        if (!empty($cod)) {
            $condicion .= " AND AR.Codigo LIKE '%$cod%'";
        }
        if (!empty($compra)) {
            $condicion .= " AND (AR.Codigo_Compra_N LIKE '%$compra%')";
        }
        if (!empty($proveedor)) {
            $condicion .= " AND AR.proveedor LIKE '%$proveedor%'";
        }
        if (!empty($fecha)) {
            $fechaInicio = trim(explode(' - ', $fecha)[0]);
            $fechaFin = trim(explode(' - ', $fecha)[1]);
            $condicion .= " AND DATE_FORMAT(AR.Fecha_Creacion, '%Y-%m-%d') BETWEEN '$fechaInicio' AND '$fechaFin'";
        }
        if (!empty($fecha2)) {
            $fechaInicio = trim(explode(' - ', $fecha2)[0]);
            $fechaFin = trim(explode(' - ', $fecha2)[1]);
            $condicion .= " AND (DATE(AR.Fecha_Compra_N) BETWEEN '$fechaInicio' AND '$fechaFin')";
        }
        if (!empty($fact)) {
            $condicion .= " AND AR.Facturas LIKE '%$fact%'";
        }
        $actaRecepciones = ActaRecepcion::select('Id_Acta_Recepcion as Id_Acta', 'Codigo', 'Estado', 'Fecha_Creacion', 'Tipo_Acta', 'image as Imagen', 'Nombre as Bodega', 'Id_Bodega_Nuevo', 'Codigo_Compra_N', 'Proveedor', 'Tipo', 'Acta_Recepcion as Tipo_Acomodar')
            ->leftJoin('people', 'people.id', '=', 'Acta_Recepcion.Identificacion_Funcionario')
            ->leftJoin('Orden_Compra_Nacional', 'Orden_Compra_Nacional.Id_Orden_Compra_Nacional', '=', 'Acta_Recepcion.Id_Orden_Compra_Nacional')
            ->leftJoin('Bodega_Nuevo as B', 'B.Id_Bodega_Nuevo', '=', 'Acta_Recepcion.Id_Bodega_Nuevo')
            ->leftJoin('third_parties as P', 'P.id', '=', 'Acta_Recepcion.Id_Proveedor')
            ->where(function ($query) use ($estadoCond, $condicion, $enbodega) {
                $query->whereRaw($estadoCond)
                    ->whereRaw($condicion)
                    ->whereRaw($enbodega);
            })
            ->paginate(request()->get('tam', 10), ['*'], 'page', request()->get('pag', 1));

        $actaRecepcionRemisiones = ActaRecepcionRemision::select('Id_Acta_Recepcion_Remision as Id_Acta', 'Codigo', 'Estado', 'Fecha as Fecha_Creacion', 'INTERNA as Tipo_Acta', 'image as Imagen', 'ARC.Id_Bodega_Nuevo', 'R.Codigo as Codigo_Remision', 'INTERNA as Proveedor', 'INTERNA as Tipo', 'Acta_Recepcion_Remision as Tipo_Acomodar')
            ->join('people as F', 'F.id', '=', 'Acta_Recepcion_Remision.Identificacion_Funcionario')
            ->join('Remision as R', 'R.Id_Remision', '=', 'Acta_Recepcion_Remision.Id_Remision')
            ->whereNotNull('ARC.Id_Bodega_Nuevo')
            ->where('ARC.Estado', '!=', 'Anulada')
            ->where(function ($query) use ($estado) {
                $query->where('ARC.Origen_Destino', 'Bodega')
                    ->orWhere('ARC.Origen_Destino', 'INTERNA');
            })
            ->where('ARC.Estado_Entrada_Bodega', $estado)
            ->paginate(request()->get('tam', 10), ['*'], 'page', request()->get('pag', 1));

        $ajusteIndividual = AjusteIndividual::select('Id_Ajuste_Individual as Id_Acta', 'Codigo', 'Estado_Entrada_Bodega as Estado', 'Fecha as Fecha_Creacion', 'INTERNA as Tipo_Acta', 'image as Imagen', 'NULL as Bodega', 'Id_Origen_Destino as Id_Bodega_Nuevo', 'INTERNA as Codigo_Compra_N', 'INTERNA as Proveedor', 'AJUSTE INDIVIDUAL as Codigo_Remision', 'INTERNA as Fecha_Compra_N', 'NULL as Id_Orden_Compra', 'INTERNA as Facturas', 'INTERNA as Tipo', 'Ajuste_Individual as Tipo_Acomodar')
            ->join('people as F', 'F.id', '=', 'Ajuste_Individual.Identificacion_Funcionario')
            ->where('Ajuste_Individual.Tipo', 'Entrada')
            ->where('Ajuste_Individual.Estado', '!=', 'Anulada')
            ->where(function ($query) use ($estado) {
                $query->where('Ajuste_Individual.Origen_Destino', 'Bodega')
                    ->orWhere('Ajuste_Individual.Origen_Destino', 'INTERNA');
            })
            ->where('Ajuste_Individual.Estado_Entrada_Bodega', $estado)
            ->paginate(request()->get('tam', 10), ['*'], 'page', request()->get('pag', 1));


        $notaCredito = NotaCredito::select('Id_Nota_Credito as Id_Acta', 'Codigo', 'Estado', 'Fecha as Fecha_Creacion', 'INTERNA as Tipo_Acta', 'image as Imagen', 'NULL as Bodega', 'Id_Bodega_Nuevo', 'INTERNA as Codigo_Compra_N', 'INTERNA as Proveedor', 'AJUSTE INDIVIDUAL as Codigo_Remision', 'INTERNA as Fecha_Compra_N', 'NULL as Id_Orden_Compra', 'INTERNA as Facturas', 'INTERNA as Tipo', 'Nota_Credito as Tipo_Acomodar')
            ->join('people as F', 'F.id', '=', 'Nota_Credito.Identificacion_Funcionario')
            ->where('Nota_Credito.Estado', $estado)
            ->whereNotNull('Nota_Credito.Id_Bodega_Nuevo')
            ->paginate(request()->get('tam', 10), ['*'], 'page', request()->get('pag', 1));

        $nacionalizacionParcial = NacionalizacionParcial::select('Id_Nacionalizacion_Parcial as Id_Acta', 'Codigo', 'Aprobada as Estado', 'Fecha_Registro as Fecha_Creacion', 'INTERNA as Tipo_Acta', 'image as Imagen', 'NULL as Bodega', 'ACI.Id_Bodega_Nuevo', 'INTERNA as Codigo_Compra_N', 'INTERNA as Proveedor', 'AJUSTE INDIVIDUAL as Codigo_Remision', 'INTERNA as Fecha_Compra_N', 'NULL as Id_Orden_Compra', 'INTERNA as Facturas', 'INTERNA as Tipo', 'Nacionalizacion_Parcial as Tipo_Acomodar')
            ->join('people as F', 'F.id', '=', 'Nacionalizacion_Parcial.Identificacion_Funcionario')
            ->join('Acta_Recepcion_Internacional as ACI', 'ACI.Id_Acta_Recepcion_Internacional', '=', 'Nacionalizacion_Parcial.Id_Acta_Recepcion_Internacional')
            ->where('Nacionalizacion_Parcial.Estado', $estado === "Aprobada" ? "Nacionalizado" : "Acomodada")
            ->whereNotNull('ACI.Id_Bodega_Nuevo')
            ->paginate(request()->get('tam', 10), ['*'], 'page', request()->get('pag', 1));

        $numReg = $actaRecepciones->total() + $actaRecepcionRemisiones->total();

        $response = [
            'actarecepciones' => $actaRecepciones->merge($actaRecepcionRemisiones)->merge($ajusteIndividual)->merge($notaCredito)->merge($nacionalizacionParcial),
            'numReg' => $numReg,
        ];

        return response()->json($response);
    }

    public function detalleActa(Request $request)
    {

        $datos = ActaRecepcion::with(
            'bodega',
            'person',
            'third',
            'causal',
            'facturas',
            'orden.activity',
            'products.product.unit',
            'products.product.packaging',
            'products.product.tax'
        )
            ->findOr($request->id, function () {
                return [];
            });

        $query = "SELECT PNC.*,
                PRD.Nombre_General as Nombre_Producto,
                ifnull(CP.Costo_Promedio, 0) as Costo_Promedio,
                ifnull(CP.Costo_Promedio, 0) as Costo,
                if(PRD.Gravado ='Si', 19, 0) as Iva,
                (ifnull(CP.Costo_Promedio, 0) * PNC.Cantidad_Pendiente *(1+(if(PRD.Gravado ='Si', 19, 0)/100))) as Subtotal,
                (ifnull(CP.Costo_Promedio, 0) * PNC.Cantidad_Pendiente ) as Total,
                PNC.Cantidad_Pendiente AS Cantidad,
                '0' AS Selected
                FROM (SELECT PNC.*, (PNC.Cantidad- ifnull(PNC.Cantidad_Nueva_Orden, 0)) AS Cantidad_Pendiente FROM Producto_No_Conforme PNC ) PNC
                INNER JOIN No_Conforme NC ON NC.Id_No_Conforme = PNC.Id_No_Conforme
                INNER JOIN Producto PRD ON PRD.Id_Producto = PNC.Id_Producto
                Left Join Costo_Promedio CP on CP.Id_Producto = PNC.Id_Producto
                WHERE NC.Estado='Pendiente'
                AND PNC.Cantidad_Pendiente >0
                AND PNC.Id_Acta_Recepcion = $datos->Id_Acta_Recepcion";
        $oCon = new consulta();
        $oCon->setTipo('Multiple');
        $oCon->setQuery($query);
        $productos_no_conforme = $oCon->getData();
        unset($oCon);
        $datos->productos_no_conforme = collect($productos_no_conforme);

        return $this->success($datos);

        /* $id_acta = (isset($_REQUEST['id']) ? $_REQUEST['id'] : '');
        $tipo = (isset($_REQUEST['Tipo'])) ? $_REQUEST['Tipo'] : false;
        $query = 'SELECT AR.*,
            (SELECT GROUP_CONCAT(F.Factura SEPARATOR " / ")
                FROM Factura_Acta_Recepcion F
                WHERE F.Id_Acta_Recepcion=AR.Id_Acta_Recepcion
                GROUP BY F.Id_Acta_Recepcion
            ) AS Factura,
            B.Nombre as Nombre_Bodega,
            (SELECT IFNULL(P.social_reason, CONCAT_WS(" ", P.first_name, P.first_surname)) FROM third_parties P WHERE P.id=AR.Id_Proveedor) AS Proveedor,
            (SELECT PAR.Codigo_Compra
                FROM Producto_Acta_Recepcion PAR
                WHERE PAR.Id_Acta_Recepcion=AR.Id_Acta_Recepcion
                GROUP BY AR.Id_Acta_Recepcion
            ) AS Codigo_Compra
            FROM Acta_Recepcion AR
            INNER JOIN Bodega_Nuevo B ON AR.Id_Bodega_Nuevo=B.Id_Bodega_Nuevo
            WHERE AR.Id_Acta_Recepcion=' . $id_acta;

        $oCon = new consulta();
        $oCon->setTipo('Multiple');
        $oCon->setQuery($query);
        $datos = $oCon->getData();
        unset($oCon);

        $query = 'SELECT Factura,
            Fecha_Factura,
            Archivo_Factura
            FROM Factura_Acta_Recepcion
            WHERE Id_Acta_Recepcion=' . $id_acta;

        $oCon = new consulta();
        $oCon->setTipo('Multiple');
        $oCon->setQuery($query);
        $facturas = $oCon->getData();
        unset($oCon);

        if (!$tipo) {
            $query3 = 'SELECT AR.*,
                B.Nombre as Nombre_Bodega,
                IFNULL(social_reason, CONCAT_WS(" ", first_name, first_surname)) as NombreProveedor,
                P.cod_dian_address as DireccionProveedor,
                P.landline as TelefonoProveedor,
                (SELECT PAR.Codigo_Compra
                    FROM Producto_Acta_Recepcion PAR
                    WHERE PAR.Id_Acta_Recepcion=AR.Id_Acta_Recepcion
                    GROUP BY AR.Id_Acta_Recepcion
                ) AS Codigo_Compra
                FROM Acta_Recepcion AR
                INNER JOIN Bodega_Nuevo B ON AR.Id_Bodega_Nuevo =B.Id_Bodega_Nuevo
                INNER JOIN third_parties P On P.id = AR.Id_Proveedor
                WHERE AR.Id_Acta_Recepcion=' . $id_acta;
        } else {
            $query3 = 'SELECT AR.*,
                PD.Nombre as Nombre_Bodega,
                IFNULL(social_reason, CONCAT_WS(" ", first_name, first_surname)) as NombreProveedor,
                P.cod_dian_address as DireccionProveedor,
                P.landline as TelefonoProveedor,
                (SELECT PAR.Codigo_Compra
                    FROM Producto_Acta_Recepcion PAR
                    WHERE PAR.Id_Acta_Recepcion=AR.Id_Acta_Recepcion
                    GROUP BY AR.Id_Acta_Recepcion
                ) AS Codigo_Compra
                FROM Acta_Recepcion AR
                INNER JOIN Punto_Dispensacion PD ON AR.Id_Punto_Dispensacion=PD.Id_Punto_Dispensacion
                INNER JOIN third_parties P On P.id = AR.Id_Proveedor
                WHERE AR.Id_Acta_Recepcion=' . $id_acta;
        }

        $oCon = new consulta();
        //$oCon->setTipo('Multiple');
        $oCon->setQuery($query3);
        $datos2 = $oCon->getData();
        unset($oCon);

        $query2 = 'SELECT P.*,
            PRD.Nombre_Comercial,
            IFNULL(CONCAT(PRD.Presentacion," ", PRD.Cantidad," ", PRD.Unidad_Medida),
            CONCAT(PRD.Nombre_Comercial)) as Nombre_Producto,
            IFNULL(POC.Cantidad,0) as Cantidad_Solicitada
            FROM Producto_Acta_Recepcion P
            INNER JOIN Producto PRD ON P.Id_Producto=PRD.Id_Producto
            LEFT JOIN Producto_Orden_Compra_Nacional POC ON POC.Id_Producto_Orden_Compra_Nacional = P.Id_Producto_Orden_compra
            WHERE P.Id_Acta_Recepcion=' . $id_acta;

        $oCon = new consulta();
        $oCon->setTipo('Multiple');
        $oCon->setQuery($query2);
        $productos_acta = $oCon->getData();
        unset($oCon);


        $id = "";
        $lotes = [];
        $i = -1;
        $j = -1;
        $k = -1;
        $subtotal = 0;
        $impuesto = 0;
        $total = 0;
        foreach ($productos_acta as $prod) {
            $j++;
            if ($id != $prod->Id_Producto) {
                $id = $prod->Id_Producto;
                if ($j > 0) {
                    $k++;
                    $productos_final[$k] = $productos_acta[$j - 1];
                    $productos_final[$k]["Lotes"] = $lotes;
                }
                $lotes = [];
                $i = 0;
                $lotes[$i]["Lote"] = $prod->Lote;
                $lotes[$i]["Fecha_Vencimiento"] = $prod->Fecha_Vencimiento;
                $lotes[$i]["Cantidad"] = (int)$prod->Cantidad;
                $lotes[$i]["Precio"] = $prod->Precio;
                $lotes[$i]["Impuesto"] = (int)$prod->Impuesto;
                $lotes[$i]["Subtotal"] = $prod->Subtotal;
            } else {
                $i++;
                $lotes[$i]["Lote"] = $prod->Lote;
                $lotes[$i]["Fecha_Vencimiento"] = $prod->Fecha_Vencimiento;
                $lotes[$i]["Cantidad"] = (int)$prod->Cantidad;
                $lotes[$i]["Precio"] = $prod->Precio;
                $lotes[$i]["Impuesto"] = (int)$prod->Impuesto;
                $lotes[$i]["Subtotal"] = $prod->Subtotal;
            }
            $subtotal += (int)$prod->Cantidad * $prod->Precio;
            $impuesto += ((int)$prod->Cantidad * $prod->Precio * ((int)$prod->Impuesto / 100));
        }
        $total += $subtotal + $impuesto;
        //$productos_final[$k + 1] = $prod;
        //$productos_final[$k + 1]->Lotes = $lotes;
        $resultado = [];

        $resultado["Datos"] = $datos;
        $resultado["Datos2"] = $datos2;
        //$resultado["Datos2"]["ConteoProductos"] = count($productos_final);
        $resultado["Datos2"]["Subtotal"] = $subtotal;
        $resultado["Datos2"]["Impuesto"] = $impuesto;
        $resultado["Datos2"]["Total"] = $total;

        $resultado["Productos"] = $productos_acta;
        //$resultado["ProductosNuevo"] = $productos_final;

        $resultado["Facturas"] = $facturas;


        return response()->json($resultado); */
    }

    public function getActividadesActa(Request $request)
    {
        $idActa = $request->input('id_acta');

        // Consultar el código del acta y el id de la orden de compra
        $acta = ActaRecepcion::where('Id_Acta_Recepcion', $idActa)->first();
        $idOrdenCompra = $acta->Id_Orden_Compra_Nacional;

        $actividades = ActividadOrdenCompra::select('Actividad_Orden_Compra.*', 'people.image')
            ->join('people', 'Actividad_Orden_Compra.Identificacion_Funcionario', '=', 'people.identifier')
            ->where('Actividad_Orden_Compra.Id_Orden_Compra_Nacional', $idOrdenCompra)
            ->orderBy('Fecha', 'ASC')
            ->get();

        $result = $actividades->map(function ($actividad) {
            $estadoActividad = '';

            switch ($actividad->Estado) {
                case 'Creacion':
                    $estadoActividad = '1 ' . $actividad->Estado;
                    break;
                case 'Recepcion':
                case 'Edicion':
                case 'Aprobacion':
                    $estadoActividad = '2 ' . $actividad->Estado;
                    break;
                default:
                    $estadoActividad = '0 Sin Estado';
                    break;
            }

            $actividad->Estado_Actividad = $estadoActividad;
            return $actividad;
        });

        return response()->json($result);
    }

    public function save(Request $request)
    {
        try {
            DB::beginTransaction();
            $data = $request->except('invoices', 'products', 'products_acta', 'invoices');
            $products_acta = $request->selected_products;
            $invoices = $request->invoices;
            $products_orden_compra = $request->products;
            $data['company_id'] = getCompanyWorkedId();

            $data['Identificacion_Funcionario'] = auth()->user()->person_id;
            if (is_array($data['Observaciones_acta']) && count($data['Observaciones_acta']) === 1 && $data['Observaciones_acta'][0] === null) {
                $data['Observaciones'] = "";
            } else {
                $data['Observaciones'] = $data['Observaciones_acta'];
            }
            if (!$request->id) {
                $data['Codigo'] = generateConsecutive('acta_recepcion');

            }
            $data['Id_Punto_Dispensacion'] = Person::find(auth()->user()->person_id)->dispensing_point_id;
            $data['tipo_compra'] = 'compras';
            $acta = ActaRecepcion::updateOrcreate(
                ['Id_Acta_Recepcion' => $data['Id_Acta_Recepcion']],
                $data

            );

            $products_received = ProductoActaRecepcion::where('Id_Producto_Orden_Compra', $acta->Id_Orden_Compra_Nacional)
                ->distinct('Id_Producto')
                ->count('Id_Producto');

            $total_products_received = $products_received + count($products_acta);

            // if ($total_products_received !== count($products_orden_compra)) {
            //     $acta->update(['Estado' => 'Parcial']);
            // }

            $ordenCompra = OrdenCompraNacional::find($acta->Id_Orden_Compra_Nacional);
            // if ($total_products_received == count($products_orden_compra)) {
            $acta->update(['Estado' => 'Creada']);
            $ordenCompra->update(['Estado' => 'Recibida']);

            // }
            if (isset($invoices)) {
                foreach ($invoices as $invoice) {
                    if ($invoice['type'] == "application/pdf") {
                        $base64 = saveBase64File($invoice["Archivo_Factura"], 'factura-acta-compra/', false, '.pdf');
                    } else {
                        $base64 = saveBase64($invoice["Archivo_Factura"], 'factura-acta-compra/', false);
                    }
                    $invoice['Archivo_Factura'] = URL::to('/') . '/api/file-view?path=' . $base64;
                    $factura = FacturaActaRecepcion::updateOrCreate(
                        ['Id_Factura_Acta_Recepcion' => $invoice['Id_Factura_Acta_Recepcion']],
                        [
                            'Id_Acta_Recepcion' => $acta['Id_Acta_Recepcion'],
                            'Factura' => $invoice['Factura'],
                            'Fecha_Factura' => $invoice['Fecha_Factura'],
                            'Archivo_Factura' => $invoice['Archivo_Factura'],
                            'Cufe' => $invoice['cufe'],
                            'Id_Orden_Compra' => $data['Id_Orden_Compra_Nacional'],
                            'Tipo_Compra' => $data['Tipo'],
                        ]
                    );
                }
            }

            // Variable para controlar si se debe insertar en No_Conforme
            $insertarNoConforme = false;
            foreach ($products_acta as $productos) {
                foreach ($productos as $product) {
                    $savedProduct = ProductoActaRecepcion::updateOrCreate(
                        ['Id_Producto_Acta_Recepcion' => $product['Id_Producto_Acta_Recepcion']],
                        [
                            'Id_Acta_Recepcion' => $acta['Id_Acta_Recepcion'], //!este no sirve
                            'Factura' => $factura['Id_Factura_Acta_Recepcion'],
                            'Cantidad' => $product['Cantidad'],
                            'Precio' => $product['Total'],
                            'Impuesto' => $product['Iva'] ?? 0,
                            'Subtotal' => $product['Subtotal'],
                            'Id_Producto' => $product['Id_Producto'],
                            'Tipo_Compra' => $data['Tipo'],
                            'Id_Causal_No_Conforme' => $product['nonConform'] ?? null,
                            'nonconforming_quantity' => $product['nonConformNum'] ?? null,
                            'Id_Producto_Orden_Compra' => $data['Id_Orden_Compra_Nacional'],
                            'Lote' => $product['lote'],
                            'Fecha_Vencimiento' => $product['fecha_vencimiento'],
                        ]
                    );

                    if (!empty($product['nonConform'])) {
                        if (!$insertarNoConforme) {
                            $savedNoconforme = NoConforme::firstOrCreate(
                                ['Id_Acta_Recepcion_Compra' => $acta->Id_Acta_Recepcion],
                                [
                                    'Codigo' => generateConsecutive('No_Conforme'),
                                    'Persona_Reporta' => Person::find(Auth()->user()->person_id)->identifier,
                                    'Tipo' => 'Compra',
                                    'Estado' => 'Pendiente',
                                ]
                            );

                            if ($savedNoconforme->wasRecentlyCreated) {
                                sumConsecutive('No_Conforme');
                            }
                        }
                        $insertarNoConforme = true;
                        ProductoNoConforme::Create(
                            [
                                'Id_Producto' => $product['Id_Producto'],
                                'Id_No_Conforme' => $savedNoconforme->Id_No_Conforme,
                                'Id_Compra' => $data['Id_Orden_Compra_Nacional'],
                                'Tipo_Compra' => 'Nacional',
                                'Id_Acta_Recepcion' => $acta->Id_Acta_Recepcion,
                                'Cantidad' => $product['nonConformNum'] ?? 0,
                                'Id_Causal_No_Conforme' => $product['nonConform'],
                            ]
                        );
                    }


                    foreach ($product['variables'] as $variable) {
                        VariableProduct::updateOrCreate(
                            ['id' => $variable['id']],
                            [
                                'product_id' => $product['Id_Producto'],
                                'subcategory_variables_id' => $variable['subcategory_id'] ?? null,
                                'category_variables_id' => $variable['category_id'] ?? null,
                                'valor' => $variable['value'],
                                'Id_Producto_Acta_Recepcion' => $savedProduct->Id_Producto_Acta_Recepcion,
                            ]
                        );
                    }
                }
            }

            $selectedProductQuantities = collect($products_acta)->flatMap(function ($productGroup) {
                return collect($productGroup)->map(function ($product) {
                    return [
                        'Id_Producto' => $product['Id_Producto'],
                        'Cantidad' => $product['Cantidad'] + $product["nonConformNum"]
                    ];
                });
            })->groupBy('Id_Producto')->map(function ($group) {
                return $group->sum('Cantidad');
            });


            $productQuantities = collect($products_orden_compra)->mapWithKeys(function ($product) {
                return [$product['Id_Producto'] => $product['Cantidad']];
            });

            $nonConformingProducts = $productQuantities->map(function ($cantidad, $idProducto) use ($selectedProductQuantities) {
                $cantidadRecibida = $selectedProductQuantities->get($idProducto, 0);
                return $cantidad - $cantidadRecibida;
            })->filter(function ($cantidadRestante) {
                return $cantidadRestante > 0;
            });


            if ($nonConformingProducts->isNotEmpty()) {
                if (!$insertarNoConforme) {
                    $savedNoconforme = NoConforme::firstOrCreate(
                        ['Id_Acta_Recepcion_Compra' => $acta->Id_Acta_Recepcion],
                        [
                            'Codigo' => generateConsecutive('No_Conforme'),
                            'Persona_Reporta' => Person::find(Auth()->user()->person_id)->identifier,
                            'Tipo' => 'Compra',
                            'Estado' => 'Pendiente',
                        ]
                    );
                    if ($savedNoconforme->wasRecentlyCreated) {
                        sumConsecutive('No_Conforme');
                    }
                }


                foreach ($nonConformingProducts as $idProducto => $cantidadRestante) {
                    ProductoNoConforme::Create([
                        'Id_Producto' => $idProducto,
                        'Id_No_Conforme' => $savedNoconforme->Id_No_Conforme,
                        'Id_Compra' => $data['Id_Orden_Compra_Nacional'],
                        'Tipo_Compra' => 'Nacional',
                        'Id_Acta_Recepcion' => $acta->Id_Acta_Recepcion,
                        'Cantidad' => $cantidadRestante,
                        'Id_Causal_No_Conforme' => null,
                        'Observaciones' => 'Producto no conforme registrado automáticamente',
                    ]);
                }
            }

            // throw new \Exception('Forzando un error para revertir la transacción');

            if (!$request->id) {
                sumConsecutive('acta_recepcion');
            }

            $oItem = new ActividadOrdenCompra();
            $oItem->Id_Orden_Compra_Nacional = $acta['Id_Orden_Compra_Nacional'];
            $oItem->Id_Acta_Recepcion_Compra = $acta['Id_Acta_Recepcion'];
            $oItem->Identificacion_Funcionario = Person::find(Auth()->user()->person_id)->id;
            $oItem->Detalles = "Se recibio el acta con codigo " . $acta['Codigo'];
            $oItem->Fecha = now();
            $oItem->Estado = 'Recepcion';

            $oItem->save();

            DB::commit();
            return $this->success('Creado con éxito');
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->error($th->getMessage(), 500);
        }
    }

    public function saveOld()
    {
        $company_id = 1;

        //Objeto de la clase que almacena los archivos
        //$storer = new FileStorer();
        //echo 'asd';exit;
        $contabilizar = new Contabilizar();
        $configuracion = new Configuracion($company_id);

        $datos = (isset($_REQUEST['datos']) ? $_REQUEST['datos'] : '');
        $productoCompra = (isset($_REQUEST['productos']) ? $_REQUEST['productos'] : '');
        $codigoCompra = (isset($_REQUEST['codigoCompra']) ? $_REQUEST['codigoCompra'] : '');
        $tipoCompra = (isset($_REQUEST['tipoCompra']) ? $_REQUEST['tipoCompra'] : '');
        $facturas = (isset($_REQUEST['facturas']) ? $_REQUEST['facturas'] : '');
        $comparar = (isset($_REQUEST['comparar']) ? $_REQUEST['comparar'] : '');
        $archivos = (isset($_REQUEST['archivos']) ? $_REQUEST['archivos'] : '');
        $no_conforme_devolucion = (isset($_REQUEST['id_no_conforme']) ? $_REQUEST['id_no_conforme'] : false);

        $datosProductos = (array) json_decode($productoCompra, true);
        $datos = (array) json_decode($datos);
        $facturas = (array) json_decode($facturas, true);
        $comparar = (array) json_decode($comparar, true);

        $datos_movimiento_contable = array();


        $prov_ret = $this->GetInfoRetencionesProveedor($datos['Id_Proveedor']);
        $columns = array_column($facturas, 'Retenciones');

        if ( /* $prov_ret['Tipo_Retencion'] == 'Permanente' || */ $prov_ret['Tipo_Reteica'] == 'Permanente') {
            if ($prov_ret['Id_Plan_Cuenta_Retefuente'] != '' || $prov_ret['Id_Plan_Cuenta_Reteica'] != '') {
                if (count($columns) == 0) {
                    $resultado['mensaje'] = "Ha ocurrido un incoveniente con las retenciones de las facturas cargadas, contacte con el administrador del sistema!";
                    $resultado['tipo'] = "error";

                    return response()->json($resultado);
                }
            }
        }

        $cod = $configuracion->getConsecutivo('Acta_Recepcion', 'Acta_Recepcion');

        $datos['Codigo'] = $cod;

        $estado = $this->ValidarCodigo($cod, $company_id);
        if ($estado) {
            $cod = $configuracion->getConsecutivo('Acta_Recepcion', 'Acta_Recepcion');
            $datos['Codigo'] = $cod;
        }

        $oCon = new consulta();

        switch ($tipoCompra) {

            case "Nacional": {
                $query = "SELECT Id_Orden_Compra_Nacional as id, Codigo, Identificacion_Funcionario, Id_Bodega_Nuevo, Id_Proveedor
                        FROM Orden_Compra_Nacional WHERE Codigo = '" . $codigoCompra . "'";

                $oCon->setQuery($query);
                $detalleCompra = $oCon->getData();
                $oCon->setTipo('Multiple');
                unset($oCon);
                break;
            }
            case "Internacional": {

                $query = "SELECT Id_Orden_Compra_Internacional as id,Codigo,I
                dentificacion_Funcionario,Id_Bodega_Nuevo,Id_Proveedor FROM Orden_Compra_Internacional
                WHERE Codigo = '" . $codigoCompra . "'";
                $oCon->setQuery($query);
                $detalleCompra = $oCon->getData();
                $oCon->setTipo('Multiple');
                unset($oCon);
                break;
            }
        }




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
        $qr = $this->generarqr('actarecepcion', $id_Acta_Recepcion, '/IMAGENES/QR/');
        $oItem = new complex("Acta_Recepcion", "Id_Acta_Recepcion", $id_Acta_Recepcion);
        $oItem->Codigo_Qr = $qr;
        $oItem->save();
        unset($oItem);
        /* HASTA AQUI GENERA QR */


        // productos

        /* var_dump($detalleCompra);
        exit; */

        // realizar guardado para las caracteristicas de los productos
        //1. revisar cuales fueron marcados y no marcados en el array que traigo.
        $i = -1;
        $contador = 0;
        $genero_no_conforme = false;
        $id_no_conforme = '';
        $productos = explode(",", $comparar['Id_Producto']);
        $id_productos_acta = array_column($datosProductos, "Id_Producto");
        //print_r( $productos );
        //rint_r( $productos );
        //print_r( $datosProductos );



        foreach ($productos as $value) {

            if (!array_search($value, $id_productos_acta)) {


                if (!$genero_no_conforme) {

                    $genero_no_conforme = true;
                    $configuracion = new Configuracion($company_id);
                    $cod = $configuracion->getConsecutivo('No_Conforme', 'No_Conforme');
                    $oItem2 = new complex('No_Conforme', 'Id_No_Conforme');
                    $oItem2->Codigo = $cod;
                    $oItem2->Persona_Reporta = $datos['Identificacion_Funcionario'];
                    $oItem2->Tipo = "Compra";
                    $oItem2->Id_Acta_Recepcion_Compra = $id_Acta_Recepcion;
                    $oItem2->save();
                    $id_no_conforme = $oItem2->getId();
                    unset($oItem2);

                    /*AQUI GENERA QR */
                    /*$qr = generarqr('noconforme',$id_no_conforme,'/IMAGENES/QR/');
                    $oItem = new complex("No_Conforme","Id_No_Conforme",$id_no_conforme);
                    $oItem->Codigo_Qr=$qr;
                    $oItem->save();
                    unset($oItem);*/
                    /* HASTA AQUI GENERA QR */
                }
                /*var_dump( [$comparar['Id_Orden_Nacional'], $value ] )  ;exit;
                exit;*/
                $query = "SELECT	Cantidad  FROM Producto_Orden_Compra_Nacional WHERE Id_Producto = " . $value . " AND Id_Orden_Compra_Nacional=" . $comparar['Id_Orden_Nacional'];

                $oCon = new consulta();
                $oCon->setQuery($query);
                $cantidad = $oCon->getData();
                unset($oCon);

                $oItem2 = new complex('Producto_No_Conforme', 'Id_Producto_No_Conforme');
                $oItem2->Id_Producto = $value;
                $oItem2->Id_No_Conforme = $id_no_conforme;
                $oItem2->Id_Compra = $datos["Id_Orden_Compra"];
                $oItem2->Tipo_Compra = $datos["Tipo"];
                $oItem2->Id_Acta_Recepcion = $id_Acta_Recepcion;
                $oItem2->Cantidad = number_format($cantidad['Cantidad'], 0, "", "");
                $oItem2->Id_Causal_No_Conforme = 2;
                $oItem2->Observaciones = "PRODUCTO NO LLEGO EN FISICO";
                $oItem2->save();
                unset($oItem2);
            }
        }
        //echo 'er444';exit;

        foreach ($datosProductos as $prod) {
            unset($prod["producto"][count($prod["producto"]) - 1]);
            foreach ($prod["producto"] as $item) {
                $i++;

                if ($item['Lote'] != '') {

                    $oItem = new complex('Producto_Acta_Recepcion', 'Id_Producto_Acta_Recepcion');
                    //mandar productos a Producto_Acta_Recepcion

                    foreach ($item as $index => $value) {

                        if ($index == 'Temperatura') {
                            $oItem->$index = number_format($value, 2, ".", "");
                        } else {
                            $oItem->$index = $value;
                        }
                    }
                    $subtotal = ((int) $item['Cantidad']) * ((int) $item['Precio']);
                    $oItem->Id_Producto_Orden_Compra = $prod["Id_Producto_Orden_Compra"];
                    $oItem->Codigo_Compra = $codigoCompra;
                    $oItem->Tipo_Compra = $tipoCompra;
                    $oItem->Id_Acta_Recepcion = $id_Acta_Recepcion;
                    // $precio = number_format($prod['Precio'],2,".","");
                    $subtotal = number_format((int) $subtotal, 2, ".", "");
                    $oItem->Subtotal = round($subtotal);
                    $oItem->save();
                    unset($oItem);
                }


                if ($item["No_Conforme"] != "") {
                    if (!$genero_no_conforme) { // Para que solo registre un solo registro por cada no conforme de productos.
                        $genero_no_conforme = true;
                        $configuracion = new Configuracion($company_id);
                        $cod = $configuracion->getConsecutivo('No_Conforme', 'No_Conforme');
                        $oItem2 = new complex('No_Conforme', 'Id_No_Conforme');
                        $oItem2->Codigo = $cod;
                        $oItem2->Persona_Reporta = $datos['Identificacion_Funcionario'];
                        $oItem2->Tipo = "Compra";
                        $oItem2->Id_Acta_Recepcion_Compra = $id_Acta_Recepcion;
                        $oItem2->save();
                        $id_no_conforme = $oItem2->getId();
                        unset($oItem2);

                        /*AQUI GENERA QR */
                        $qr = $this->generarqr('noconforme', $id_no_conforme, '/IMAGENES/QR/');
                        $oItem = new complex("No_Conforme", "Id_No_Conforme", $id_no_conforme);
                        $oItem->Codigo_Qr = $qr;
                        $oItem->save();
                        unset($oItem);
                        /*HASTA AQUI GENERA QR */
                    }

                    $oItem2 = new complex('Producto_No_Conforme', 'Id_Producto_No_Conforme');
                    $oItem2->Id_Producto = $item["Id_Producto"];
                    $oItem2->Id_No_Conforme = $id_no_conforme;
                    $oItem2->Id_Compra = $datos["Id_Orden_Compra"];
                    $oItem2->Tipo_Compra = $datos["Tipo"];
                    $oItem2->Id_Acta_Recepcion = $id_Acta_Recepcion;
                    $oItem2->Cantidad = $item["Cantidad_No_Conforme"];
                    $oItem2->Id_Causal_No_Conforme = $item['No_Conforme'];
                    $oItem = new complex("Causal_No_Conforme", "Id_Causal_No_Conforme", $item['No_Conforme']);
                    $oItem2->Observaciones = $oItem->Nombre;
                    unset($oItem);
                    $oItem2->save();
                    unset($oItem2);
                }

                // ACA SE AGREGA EL PRODUCTO A LA LISTA DE GANANCIA
                $query = 'SELECT LG.Porcentaje, LG.Id_Lista_Ganancia FROM Lista_Ganancia LG';
                $oCon = new consulta();
                $oCon->setQuery($query);
                $oCon->setTipo('Multiple');
                $porcentaje = $oCon->getData();
                unset($oCon);
                //datos
                //$cum_producto = $this->GetCodigoCum($item['Id_Producto']);
                foreach ($porcentaje as $value) {
                    $query = 'SELECT * FROM Producto_Lista_Ganancia WHERE Id_lista_Ganancia=' . $value->Id_Lista_Ganancia;
                    $oCon = new consulta();
                    $oCon->setQuery($query);
                    $cum = $oCon->getData();
                    unset($oCon);
                    if ($cum) {
                        $precio = number_format($item['Precio'] / ((100 - $value->Porcentaje) / 100), 0, '.', '');
                        if ($cum['Precio'] < $precio) {
                            $oItem = new complex('Producto_Lista_Ganancia', 'Id_Producto_Lista_Ganancia', $cum['Id_Producto_Lista_Ganancia']);

                            $oItem->Precio = $precio;
                            $oItem->Id_Lista_Ganancia = $value->Id_Lista_Ganancia;
                            $oItem->save();
                            unset($oItem);
                        }
                    } else {
                        $oItem = new complex('Producto_Lista_Ganancia', 'Id_Producto_Lista_Ganancia');
                        //$oItem->Cum = $cum_producto;
                        $precio = number_format($item['Precio'] / ((100 - $value->Porcentaje) / 100), 0, '.', '');
                        $oItem->Precio = $precio;
                        $oItem->Id_Lista_Ganancia = $value->Id_Lista_Ganancia;
                        $oItem->save();
                        unset($oItem);
                    }
                }
            }
        }


        //NUEVO METODO PARA GUARDAR LOS ARCHIVOS
        // if (!empty($_FILES) && count($_FILES) > 0){
        // $facturas_files = array();
        // $productos_files = array();
        // foreach ($_FILES as $key => $value) {
        //     if (strpos($key, 'archivos')) {
        //         array_push($facturas_files, $value);
        //     }else{
        //         array_push($productos_files, $value);
        //     }
        // }
        //     //GUARDAR ARCHIVO Y RETORNAR NOMBRE DEL MISMO
        //     $nombres_archivos = $storer->UploadFileToRemoteServer($facturas_files, 'store_remote_files', 'ARCHIVOS/FACTURAS_COMPRA/');
        // }

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

                $_file1 = Storage::put("ARCHIVOS/FACTURAS_COMPRA/", $_filename1);

                $subido1 = move_uploaded_file($_FILES["archivos$i"]['tmp_name'], $_file1);
                if ($subido1) {
                    @chmod($_file1, 0777);
                    $oItem->Archivo_Factura = $_filename1;
                }
            }

            //HABILITAR ESTA LINEA PARA COLOCAR EL NOMBRE DEL ARCHIVO DESDE EL ARRAY QUE VIENE DEL NUEVO METODO PARA GUARDAR LOS ARCHIVOS
            // $oItem->Archivo_Factura = $nombres_archivos[$i];

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

            if ($oItem->Id_Subcategoria != $value["Id_Subcategoria"]) {
                $oItem->Id_Subcategoria = $value["Id_Subcategoria"];
            }
            if (isset($_FILES["fotos$h"]['name'])) {
                $posicion2 = strrpos($_FILES["fotos$h"]['name'], '.') + 1;
                $extension2 = substr($_FILES["fotos$h"]['name'], $posicion2);
                $extension2 = strtolower($extension2);
                $_filename2 = uniqid() . "." . $extension2;
                $_file2 = Storage::put("IMAGENES/PRODUCTOS/", $_filename2);

                $subido2 = move_uploaded_file($_FILES["fotos$h"]['tmp_name'], $_file2);
                if ($subido2) {
                    @chmod($_file2, 0777);
                    $oItem->Imagen = $_filename2;
                }
            }
            $oItem->save();
            unset($oItem);
        }

        //cambiar el estado de la compra a RECIBIDA
        switch ($tipoCompra) {

            case "Nacional": {
                $oItem = new complex('Orden_Compra_Nacional', 'Id_Orden_Compra_Nacional', $detalleCompra['id']);
                $oItem->getData();
                $oItem->Estado = "Recibida";
                $oItem->save();
                unset($oItem);
                break;
            }
            case "Internacional": {
                $oItem = new complex('Orden_Compra_Internacional', 'Id_Orden_Compra_Internacional', $detalleCompra['id']);
                $oItem->getData();
                $oItem->Estado = "Recibida";
                $oItem->save();
                unset($oItem);
                break;
            }
        }
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
        $oItem->Identificacion_Funcionario = $datos['Identificacion_Funcionario'];
        $oItem->Detalles = "Se recibio el acta con codigo " . $acta_data['Codigo'];
        $oItem->Fecha = date("Y-m-d H:i:s");
        $oItem->Estado = 'Recepcion';
        $oItem->save();
        unset($oItem);

        if ($no_conforme_devolucion) {
            $oItem = new complex('No_Conforme', 'Id_No_Conforme', $no_conforme_devolucion);
            $oItem->Estado = 'Cerrado';
            $oItem->save();
            unset($oItem);
        }


        //GUARDAR MOVMIMIENTO CONTABLE ACTA*/
        $datos_movimiento_contable['Id_Registro'] = $id_Acta_Recepcion;
        $datos_movimiento_contable['Numero_Comprobante'] = $datos['Codigo'];
        $datos_movimiento_contable['Nit'] = $datos['Id_Proveedor'];
        $datos_movimiento_contable['Productos'] = $datosProductos;
        $datos_movimiento_contable['Facturas'] = $facturas;

        //$contabilizar->CrearMovimientoContable('Acta Recepcion', $datos_movimiento_contable);

        return response()->json($resultado);
    }
    function GetCodigoCum($id_producto)
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

    function ValidarCodigo($codigo, $company_id)
    {
        $estado = false;

        $query = 'SELECT
            Codigo
        FROM Acta_Recepcion
        WHERE
            Codigo = "' . $codigo . '"';

        $oCon = new consulta();
        $oCon->setQuery($query);
        $acta = $oCon->getData();
        unset($oCon);
        if ($acta && $acta['Codigo']) {
            $estado = true;
        }
        return $estado;
    }

    function GetInfoRetencionesProveedor($idProveedor)
    {
        /* retention_type as Tipo_Retencion, */
        $query = "
            SELECT
            reteica_type as Tipo_Reteica,
            reteica_account_id as Id_Plan_Cuenta_Reteica,
            retefuente_account_id as Id_Plan_Cuenta_Retefuente
            FROM third_parties
            WHERE
                id = '$idProveedor'";

        //CONDICIONES ADICIONALES
        // AND (Tipo_Retencion IS NOT NULL AND Tipo_Retencion <> 'N/A' AND Tipo_Retencion <> 'Autorretenedor')
        // AND (Tipo_Reteica IS NOT NULL AND Tipo_Reteica <> 'N/A')
        // AND (Id_Plan_Cuenta_Retefuente IS NOT NULL || Id_Plan_Cuenta_Reteica IS NOT NULL)

        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->setTipo('simple');
        $proveedor = $oCon->getData();
        unset($oCon);

        return $proveedor;
    }
    function generarqr($tipo, $id, $ruta)
    {
        $errorCorrectionLevel = 'H';
        $matrixPointSize = min(max((int) 5, 1), 10);
        $nombre = md5($tipo . '|' . $id . '|' . $errorCorrectionLevel . '|' . $matrixPointSize) . '.png';
        $filename = $_SERVER["DOCUMENT_ROOT"] . "/" . $ruta . $nombre;

        return ($nombre);
    }

    function actualizarCostoPromedio($id_acta_recepcion, $acta_data, $funcionario)
    {
        $query = "SELECT PA.Id_Producto, SUM(PA.Cantidad) AS Cantidad, PA.Precio,
         P.Codigo_Cum AS Cum
        FROM Producto_Acta_Recepcion PA
        INNER JOIN Producto P ON P.Id_Producto= PA.Id_Producto
        WHERE Id_Acta_Recepcion = $id_acta_recepcion
         GROUP BY P.Id_Producto";
        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->setTipo('Multiple');
        $productos = $oCon->getData();
        unset($oItem);

        foreach ($productos as $producto) {
            # code...
            $costopromedio = new CostoPromedio($producto["Id_Producto"], $producto["Cantidad"], $producto["Precio"]);
            $costopromedio->actualizarCostoPromedio();
            unset($costopromedio);

            $this->actualizarListaGanancia($producto, $acta_data, $funcionario);
        }

    }

    function guardarActividad($id_acta_recepcion, $acta_data, $funcionario)
    {
        $oItem = new complex('Actividad_Orden_Compra', "Id_Acta_Recepcion_Compra");
        $oItem->Id_Orden_Compra_Nacional = $acta_data['Id_Orden_Compra_Nacional'];
        $oItem->Id_Acta_Recepcion_Compra = $id_acta_recepcion;
        $oItem->Identificacion_Funcionario = $funcionario;
        $oItem->Detalles = "Se aprobo y se ingreso el Acta con codigo " . $acta_data['Codigo'];
        $oItem->Fecha = date("Y-m-d H:i:s");
        $oItem->Estado = 'Aprobacion';
        $oItem->save();
        unset($oItem);
    }

    function aprobarActaFunc($id_acta_recepcion, $costos)
    {

        $oItem = new complex('Acta_Recepcion', 'Id_Acta_Recepcion', $id_acta_recepcion);
        $oItem->Estado = 'Aprobada';
        $oItem->Afecta_Costo = $costos;
        $oItem->save();

        unset($oItem);
    }

    function actualizarListaGanancia($item, $acta_data, $funcionario)
    {
        // ACA SE AGREGA EL PRODUCTO A LA LISTA DE GANANCIA
        $query = 'SELECT LG.Porcentaje, LG.Id_Lista_Ganancia FROM Lista_Ganancia LG';
        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->setTipo('Multiple');
        $porcentaje = $oCon->getData();
        unset($oCon);
        //datos
        $cum_producto = $item['Cum'];
        foreach ($porcentaje as $value) {
            $query = 'SELECT * FROM Producto_Lista_Ganancia WHERE Cum="' . $cum_producto . '" AND Id_lista_Ganancia=' . $value['Id_Lista_Ganancia'];
            $oCon = new consulta();
            $oCon->setQuery($query);
            $cum = $oCon->getData();
            unset($oCon);
            $precio_entrada = number_format($item['Precio'] / ((100 - $value['Porcentaje']) / 100), 0, '.', '');
            if ($cum) {
                if ($precio_entrada > $cum['Precio']) {
                    $oItem = new complex('Producto_Lista_Ganancia', 'Id_Producto_Lista_Ganancia', $cum['Id_Producto_Lista_Ganancia']);

                    $oItem->Precio = $precio_entrada;
                    $oItem->Id_Lista_Ganancia = $value['Id_Lista_Ganancia'];
                    $oItem->save();
                    unset($oItem);
                    $id_producto_Ganancia = $cum['Id_Producto_Lista_Ganancia'];
                    $this->guardarActListaGanancia($id_producto_Ganancia, $precio_entrada, $cum['Precio'], $acta_data, $funcionario);
                }

            } else {
                $oItem = new complex('Producto_Lista_Ganancia', 'Id_Producto_Lista_Ganancia');
                $oItem->Cum = $cum_producto;
                $oItem->Precio = $precio_entrada;
                $oItem->Id_Lista_Ganancia = $value['Id_Lista_Ganancia'];
                $oItem->save();
                $id_producto_Ganancia = $oItem->getId();
                unset($oItem);
                $this->guardarActListaGanancia($id_producto_Ganancia, $precio_entrada, 0, $acta_data, $funcionario);
            }
        }
    }

    function guardarActListaGanancia($id_producto_Lista, $precio_entrada, $precio_anterior, $funcionario, $acta_data)
    {
        $oCon = new complex("Actividad_Producto_Lista_Ganancia", "Id_Actividad_Producto_Lista_Ganancia");
        $oCon->Identificacion_Funcionario = $funcionario;
        $oCon->Id_Producto_Lista_Ganancia = $id_producto_Lista;
        $oCon->Precio_Actual = $precio_anterior;
        $oCon->Precio_Nuevo = $precio_entrada;
        $oCon->Fecha = date("Y-m-d H:i:s");
        $oCon->Detalle = $acta_data['Codigo'];
        $oCon->save();
    }

    public function aprobarActa()
    {
        $id_acta_recepcion = isset($_REQUEST['id']) ? $_REQUEST['id'] : false;
        $id_bodega_nuevo = isset($_REQUEST['Id_Bodega_Nuevo']) ? $_REQUEST['Id_Bodega_Nuevo'] : false;
        $funcionario = isset($_REQUEST['funcionario']) ? $_REQUEST['funcionario'] : false;
        try {
            $id_acta_recepcion = isset($_REQUEST['id']) ? $_REQUEST['id'] : false;
            $funcionario = isset($_REQUEST['funcionario']) ? $_REQUEST['funcionario'] : false;
            $costos = isset($_REQUEST['costos']) ? $_REQUEST['costos'] : false;
            if ($id_acta_recepcion) {
                //Consultar el codigo del acta y el id de la orden de compra
                $query_codido_acta = '
                SELECT
                    ar.Codigo,
                    ar.Id_Orden_Compra_Nacional,
                    ar.Id_Bodega_Nuevo,
                    ar.Id_Punto_Dispensacion,
                    cn.Id_Categoria_Nueva,
                    cn.Nombre as Nombre_Categoria,
                    cn.is_stackable,
                    cn.is_inventory
                FROM
                    Acta_Recepcion ar
                LEFT JOIN
                    Producto_Acta_Recepcion par ON ar.Id_Acta_Recepcion = par.Id_Acta_Recepcion
                LEFT JOIN
                    Producto p ON par.Id_Producto = p.Id_Producto
                LEFT JOIN
                    Categoria_Nueva cn ON p.Id_Categoria = cn.Id_Categoria_Nueva
                WHERE
                    ar.Id_Acta_Recepcion = ' . $id_acta_recepcion . '
                LIMIT 1';

                $oCon = new consulta();
                $oCon->setQuery($query_codido_acta);
                $acta_data = $oCon->getData();
                unset($oCon);
                if ($acta_data) {

                    $is_stackable = $acta_data['is_stackable'];
                    $is_inventory = $acta_data['is_inventory'];
                    $switch_key = $is_stackable . '-' . $is_inventory;

                    switch ($switch_key) {
                        // case '1-1':
                        //     // Proceso cuando is_stackable es 1 y is_inventory es 1
                        //     break;
                        // case '1-0':
                        //     // Proceso cuando is_stackable es 1 y is_inventory es 0
                        //     break;
                        case '0-1':
                            $this->inventariarProductos($id_acta_recepcion, $acta_data['Id_Bodega_Nuevo'], $acta_data['Id_Punto_Dispensacion']);
                            $resultado['mensaje'] = "Se ha aprobado e ingresado correctamente el acta al inventario";
                            $resultado['tipo'] = "success";
                            $resultado['titulo'] = "Operación exitosa";
                            break;
                        case '0-0':
                            $acta = ActaRecepcion::find($id_acta_recepcion);
                            $acta->estado = 'Acomodada';
                            $acta->save();
                            $resultado['mensaje'] = "Se ha aprobado el acta";
                            $resultado['tipo'] = "success";
                            $resultado['titulo'] = "Operación exitosa";
                            break;
                        default:
                            $this->aprobarActaFunc($id_acta_recepcion, $costos);

                            //actualizar costo promedio Y Listas de ganancias por cada producto
                            if ($costos == "si") {
                                $this->actualizarCostoPromedio($id_acta_recepcion, $acta_data, $funcionario);
                            }

                            //Guardando paso en el seguimiento del acta en cuestion
                            $this->guardarActividad($id_acta_recepcion, $acta_data, $funcionario);


                            $resultado['mensaje'] = "Se ha aprobado e ingresado correctamente el acta al inventario";
                            $resultado['tipo'] = "success";
                            $resultado['titulo'] = "Operación exitosa";
                            break;
                    }


                } else {
                    $resultado['mensaje'] = "Ha ocurrido un error inesperado. Por favor intentelo de nuevo";
                    $resultado['tipo'] = "error";
                    $resultado['titulo'] = "Error";
                }

                return response()->json($resultado);

            }
        } catch (\Throwable $th) {
            return response()->json($th->getMessage());
        }
    }

    public function anularActa()
    {
        $queryObj = new QueryBaseDatos();
        $response = array();
        $http_response = new HttpResponse();
        //$contabilizar = new Contabilizar();
        $modelo = (isset($_REQUEST['modelo']) ? $_REQUEST['modelo'] : '');
        $modelo = json_decode($modelo, true);
        $oItem = new complex("Acta_Recepcion", "Id_Acta_Recepcion", $modelo['Id_Acta_Recepcion']);
        $data = $oItem->getData();
        $fecha = date('Y-m-d', strtotime($data['Fecha_Creacion']));
        //if ($contabilizar->validarMesOrAnioCerrado($fecha)) {
        if (true) {
            $oItem->Estado = "Anulada";
            $oItem->Id_Causal_Anulacion = $modelo['Id_Causal_Anulacion'];
            $oItem->Observaciones_Anulacion = $modelo['Observaciones'];
            $oItem->Funcionario_Anula = $modelo['Identificacion_Funcionario'];
            $oItem->Fecha_Anulacion = date("Y-m-d H:i:s");
            $oItem->save();
            unset($oItem);
            $query = 'SELECT *
                FROM  Actividad_Orden_Compra
                WHERE Detalles LIKE "Se recibio el acta%" AND  Id_Acta_Recepcion_Compra = ' . $modelo['Id_Acta_Recepcion'];
            $queryObj->SetQuery($query);
            $actividad = $queryObj->ExecuteQuery('simple');
            $oItem = new complex("Actividad_Orden_Compra", "Id_Actividad_Orden_Compra", $actividad['Id_Actividad_Orden_Compra']);
            $oItem->delete();
            unset($oItem);
            $oItem = new complex("Orden_Compra_Nacional", "Id_Orden_Compra_Nacional", $actividad['Id_Orden_Compra_Nacional']);
            $oItem->Estado = "Pendiente";
            $oItem->save();
            unset($oItem);
            $this->AnularMovimientoContable($modelo['Id_Acta_Recepcion']);
            $http_response->SetRespuesta(0, 'Registro exitoso', 'Se ha anulado correctamente el acta de recepcion');
            $response = $http_response->GetRespuesta();
        } /* else {

$http_response->SetRespuesta(3, 'No es posible', 'No es posible anular esta acta debido a que el mes o el año del documento ha sido cerrado contablemente. Si tienes alguna duda por favor comunicarse con el departamento de contabilidad.');
$response = $http_response->GetRespuesta();
} */

        return response()->json($response);
    }

    function AnularMovimientoContable($idRegistroModulo)
    {
        //  global $contabilizar;

        //$contabilizar->AnularMovimientoContable($idRegistroModulo, 15);
    }

    public function descargarPdf(Request $request)
    {
        $tipo = $request->tipo;
        $id = $request->id;
    }

    public function listaActaRecepcion(Request $request)
    {
        try {
            $company_id = $request->input('company_id');
            $enbodega = '';
            $id_funcionario = $request->input('id_funcionario', '');
            $tipo = $request->input('tipo', '');

            if ($tipo != "General") {
                $bodegas_funcionario = FuncionarioBodegaNuevo::where('Identificacion_Funcionario', $id_funcionario)
                    ->pluck('Id_Bodega_Nuevo')
                    ->toArray();
                if (count($bodegas_funcionario) > 0) {
                    $enbodega = '(AR.Id_Bodega_Nuevo IN(' . implode(',', $bodegas_funcionario) . ') OR AR.Id_Bodega_Nuevo = 0)';
                } else {
                    $enbodega = 'AR.Id_Bodega_Nuevo = 0';
                }
            }

            $estado = $request->input('estado', '');
            $estadoCond = '';
            if ($estado != "") {
                if ($estado == 'Acomodada') {
                    $estadoCond = ' (AR.Estado = "' . $estado . '" OR AR.Fecha_Creacion < "2020-07-22") ';
                } elseif ($estado == 'Aprobada') {
                    $estadoCond = ' AR.Estado = "' . $estado . '" AND AR.Fecha_Creacion > "2020-07-22" ';
                }
            }

            $tamPag = 10;
            $paginaAct = $request->input('page', 1);

            $query = ActaRecepcion::select(DB::raw('AR.*'))
                ->fromSub(function ($query) use ($company_id, $estado) {
                    // Primera parte de la consulta para Acta Recepción
                    $query->select(
                        'ARC.Id_Acta_Recepcion AS Id_Acta',
                        'ARC.Codigo',
                        'ARC.Estado',
                        'ARC.Fecha_Creacion',
                        'ARC.Tipo_Acta',
                        'F.image AS Imagen',
                        'B.Nombre as Bodega',
                        'B.Id_Bodega_Nuevo AS Id_Bodega_Nuevo',
                        'OCN.Codigo as Codigo_Compra_N',
                        DB::raw('CONCAT(IFNULL(P.social_reason, CONCAT(P.first_name, P.first_surname) )) as Proveedor'),
                        'P.nit as Nit_Proveedor',
                        'P.dv as DV_Proveedor',
                        DB::raw('NULL as Codigo_Remision'),
                        'OCN.created_at as Fecha_Compra_N',
                        DB::raw('(CASE WHEN ARC.Tipo = "Nacional" THEN ARC.Id_Orden_Compra_Nacional ELSE ARC.Id_Orden_Compra_Internacional END) AS Id_Orden_Compra'),
                        DB::raw('(SELECT GROUP_CONCAT(Factura) FROM Factura_Acta_Recepcion WHERE Id_Acta_Recepcion = ARC.Id_Acta_Recepcion) as Facturas'),
                        'ARC.Tipo',
                        DB::raw('"Acta_Recepcion" as Tipo_Acomodar')
                    )
                        ->from('Acta_Recepcion as ARC')
                        ->leftJoin('people as F', 'F.identifier', '=', 'ARC.Identificacion_Funcionario')
                        ->leftJoin('Orden_Compra_Nacional as OCN', 'OCN.Id_Orden_Compra_Nacional', '=', 'ARC.Id_Orden_Compra_Nacional')
                        ->leftJoin('Bodega_Nuevo as B', 'B.Id_Bodega_Nuevo', '=', 'ARC.Id_Bodega_Nuevo')
                        ->join('third_parties as P', 'P.id', '=', 'ARC.Id_Proveedor')
                        ->where('ARC.tipo_compra', 'compras')
                        ->where('ARC.company_id', $company_id);



                    // Segunda parte de la consulta para Acta Recepción Remisión
                    $query->unionAll(
                        ActaRecepcionRemision::query()
                            ->select(
                                'ARC.Id_Acta_Recepcion_Remision AS Id_Acta',
                                'ARC.Codigo',
                                'ARC.Estado',
                                'ARC.Fecha AS Fecha_Creacion',
                                DB::raw('"INTERNA" as Tipo_Acta'),
                                'F.image AS Imagen',
                                DB::raw('NULL as Bodega'),
                                'ARC.Id_Bodega_Nuevo',
                                DB::raw('"INTERNA" as Codigo_Compra_N'),
                                DB::raw('"INTERNA" as Proveedor'),
                                DB::raw('NULL as Nit_Proveedor'),
                                DB::raw('NULL as DV_Proveedor'),
                                'R.Codigo as Codigo_Remision',
                                DB::raw('"INTERNA" as Fecha_Compra_N'),
                                DB::raw('NULL AS Id_Orden_Compra'),
                                DB::raw('NULL AS Facturas'),
                                DB::raw('"INTERNA" as Tipo'),
                                DB::raw('"Acta_Recepcion_Remision" as Tipo_Acomodar')
                            )
                            ->from('Acta_Recepcion_Remision as ARC')
                            ->join('people as F', 'F.id', '=', 'ARC.Identificacion_Funcionario')
                            ->join('Remision as R', 'ARC.Id_Remision', '=', 'R.Id_Remision')
                            ->whereNotNull('ARC.Id_Bodega_Nuevo')
                            ->where('ARC.company_id', $company_id)
                    );

                    // Tercera parte de la consulta para Ajuste Individual
                    $query->unionAll(
                        AjusteIndividual::query()
                            ->select(
                                'AI.Id_Ajuste_Individual AS Id_Acta',
                                'AI.Codigo',
                                DB::raw('AI.Estado_Entrada_Bodega AS Estado'),
                                'AI.Fecha AS Fecha_Creacion',
                                DB::raw('"INTERNA" AS Tipo_Acta'),
                                'F.image AS Imagen',
                                DB::raw('NULL as Bodega'),
                                'AI.Id_Origen_Destino AS Id_Bodega_Nuevo',
                                DB::raw('"INTERNA" AS Codigo_Compra_N'),
                                DB::raw('"INTERNA" AS Proveedor'),
                                DB::raw('NULL as Nit_Proveedor'),
                                DB::raw('NULL as DV_Proveedor'),
                                DB::raw('"AJUSTE INDIVIDUAL" AS Codigo_Remision'),
                                DB::raw('"INTERNA" AS Fecha_Compra_N'),
                                DB::raw('NULL AS Id_Orden_Compra'),
                                DB::raw('"INTERNA" AS Facturas'),
                                DB::raw('"INTERNA" AS Tipo'),
                                DB::raw('"Ajuste_Individual" AS Tipo_Acomodar')
                            )
                            ->from('Ajuste_Individual AS AI')
                            ->join('people AS F', 'F.id', '=', 'AI.Identificacion_Funcionario')
                            ->where('AI.Tipo', 'Entrada')
                            ->where('AI.Estado', '!=', 'Anulada')
                            ->where(function ($query) {
                            $query->where('AI.Origen_Destino', 'Bodega')
                                ->orWhere('AI.Origen_Destino', 'INTERNA');
                        })
                            ->where('AI.Estado_Entrada_Bodega', $estado)
                            ->whereNotNull('AI.Id_Origen_Destino')
                            ->where('AI.company_id', $company_id)
                    );

                    // Cuarta parte de la consulta para Nota de Crédito
                    $query->unionAll(
                        NotaCredito::query()
                            ->select(
                                'NC.Id_Nota_Credito AS Id_Acta',
                                'NC.Codigo',
                                'NC.Estado',
                                'NC.Fecha AS Fecha_Creacion',
                                DB::raw('"INTERNA" AS Tipo_Acta'),
                                'F.image AS Imagen',
                                DB::raw('NULL AS Bodega'),
                                'NC.Id_Bodega_Nuevo',
                                DB::raw('"INTERNA" AS Codigo_Compra_N'),
                                DB::raw('"INTERNA" AS Proveedor'),
                                DB::raw('NULL as Nit_Proveedor'),
                                DB::raw('NULL as DV_Proveedor'),
                                DB::raw('"AJUSTE INDIVIDUAL" AS Codigo_Remision'),
                                DB::raw('"INTERNA" AS Fecha_Compra_N'),
                                DB::raw('NULL AS Id_Orden_Compra'),
                                DB::raw('"INTERNA" AS Facturas'),
                                DB::raw('"INTERNA" AS Tipo'),
                                DB::raw('"Nota_Credito" AS Tipo_Acomodar')
                            )
                            ->from('Nota_Credito AS NC')
                            ->join('people AS F', 'F.id', '=', 'NC.Identificacion_Funcionario')
                            ->where('NC.Estado', $estado)
                            ->whereNotNull('NC.Id_Bodega_Nuevo')
                    );

                    // Quinta parte de la consulta para Nacionalización Parcial
                    $query->unionAll(
                        NacionalizacionParcial::query()
                            ->select(
                                'PAI.Id_Nacionalizacion_Parcial AS Id_Acta',
                                'PAI.Codigo',
                                DB::raw('"Aprobada" AS Estado'),
                                'PAI.Fecha_Registro AS Fecha_Creacion',
                                DB::raw('"INTERNA" AS Tipo_Acta'),
                                'F.image AS Imagen',
                                DB::raw('NULL AS Bodega'),
                                'ACI.Id_Bodega_Nuevo',
                                DB::raw('"INTERNA" AS Codigo_Compra_N'),
                                DB::raw('"INTERNA" AS Proveedor'),
                                DB::raw('NULL as Nit_Proveedor'),
                                DB::raw('NULL as DV_Proveedor'),
                                DB::raw('"AJUSTE INDIVIDUAL" AS Codigo_Remision'),
                                DB::raw('"INTERNA" AS Fecha_Compra_N'),
                                DB::raw('NULL AS Id_Orden_Compra'),
                                DB::raw('"INTERNA" AS Facturas'),
                                DB::raw('"INTERNA" AS Tipo'),
                                DB::raw('"Nacionalizacion_Parcial" AS Tipo_Acomodar')
                            )
                            ->from('Nacionalizacion_Parcial AS PAI')
                            ->join('people AS F', 'F.id', '=', 'PAI.Identificacion_Funcionario')
                            ->join('Acta_Recepcion_Internacional AS ACI', 'ACI.Id_Acta_Recepcion_Internacional', '=', 'PAI.Id_Acta_Recepcion_Internacional')
                            ->where('PAI.Estado', $estado == "Aprobada" ? "Nacionalizado" : "Acomodada")
                            ->whereNotNull('ACI.Id_Bodega_Nuevo')
                            ->where('PAI.company_id', $company_id)
                    );
                }, 'AR')
                ->whereRaw("(AR.Tipo_Acta = 'Bodega' OR AR.Tipo_Acta = 'INTERNA')")
                //!SE COMENTA ESTE FILTRO DADO QUE NO TENEMOS FUNCIONARIO POR BODEGA IMPLEMENTADO EN NUESTRO MODELO
                /* ->when(!empty($enbodega), function ($query) use ($enbodega) {
                    return $query->whereRaw($enbodega);
                }) */
                ->when(!empty($estadoCond), function ($query) use ($estadoCond) {
                    return $query->whereRaw($estadoCond);
                })
                ->when($request->filled('cod'), function ($query) use ($request) {
                    return $query->where('AR.Codigo', 'like', '%' . $request->input('cod') . '%');
                })
                ->when($request->filled('compra'), function ($query) use ($request) {
                    return $query->where('AR.Codigo_Compra_N', 'like', '%' . $request->input('compra') . '%');
                })
                ->when($request->filled('proveedor'), function ($query) use ($request) {
                    return $query->where('AR.proveedor', 'like', '%' . $request->input('proveedor') . '%');
                })
                ->when($request->filled('fecha'), function ($query) use ($request) {
                    $fechas = explode(' - ', $request->input('fecha'));
                    $fecha_inicio = trim($fechas[0]);
                    $fecha_fin = trim($fechas[1]);
                    return $query->whereBetween('AR.Fecha_Creacion', [$fecha_inicio, $fecha_fin]);
                })
                ->when($request->filled('fecha2'), function ($query) use ($request) {
                    $fechas2 = explode(' - ', $request->input('fecha2'));
                    $fecha_inicio2 = trim($fechas2[0]);
                    $fecha_fin2 = trim($fechas2[1]);
                    return $query->whereBetween('AR.Fecha_Compra_N', [$fecha_inicio2, $fecha_fin2]);
                })
                ->when($request->filled('fact'), function ($query) use ($request) {
                    return $query->where('AR.Facturas', 'like', '%' . $request->input('fact') . '%');
                })
                ->orderBy('Fecha_Creacion', 'DESC')
                ->orderBy('Codigo', 'DESC')
                ->paginate($tamPag, ['*'], 'page', $paginaAct);

            return $this->success($query);
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), $th->getCode());
        }

    }

    public function detallePerfil()
    {
        $id = request()->input('funcionario', ''); // Obtener el valor de 'funcionario'

        $permisos = People::where('id', $id)->exists(); // Verifica si hay registros en People con el id dado

        $status = $permisos ? true : false; // Establece el estado según la presencia de permisos

        return response()->json($status);
    }

    public function listaCausalesNoConforme()
    {
        $lista = CausalNoConforme::get();
        return $this->success($lista);
    }

    public function detalleAcomodar(Request $request)
    {
        $id_acta = $request->input('id', '');
        $tipo_acta = $request->input('Tipo_Acta', false);
        $datos = [];
        switch ($tipo_acta) {
            case 'Acta_Recepcion':
                $query3 = 'SELECT AR.*,
                    IFNULL(B.Nombre, PD.Nombre) as Nombre_Bodega,
                    IFNULL(B.Id_Bodega_Nuevo, PD.Id_Punto_Dispensacion) as Id_Origen_Destino,
                    IFNULL(P.social_reason, CONCAT_WS(" ", P.first_name, P.first_surname)) as NombreProveedor,
                    P.cod_dian_address as DireccionProveedor,
                    P.cell_phone as TelefonoProveedor,
                    (SELECT PAR.Codigo_Compra FROM Producto_Acta_Recepcion PAR WHERE PAR.Id_Acta_Recepcion=AR.Id_Acta_Recepcion GROUP BY AR.Id_Acta_Recepcion) AS Codigo_Compra
                    FROM Acta_Recepcion AR
                    LEFT JOIN Bodega_Nuevo B ON AR.Id_Bodega_Nuevo =B.Id_Bodega_Nuevo
                    LEFT JOIN Punto_Dispensacion PD ON AR.Id_Punto_Dispensacion = PD.Id_Punto_Dispensacion
                    INNER JOIN third_parties P On P.id = AR.Id_Proveedor
                    WHERE AR.Id_Acta_Recepcion=' . $id_acta;
                $oCon = new consulta();
                $oCon->setQuery($query3);
                $datos = $oCon->getData();
                unset($oCon);
                break;
            case 'Acta_Recepcion_Remision':
                $query = 'SELECT AR.*, AR.Fecha AS "Fecha_Creacion",
                    "INTERNA" TelefonoProveedor , "INTERNA" DireccionProveedor, "INTERNA" NombreProveedor,
                    IFNULL(B.Nombre, P.Nombre) AS Nombre_Bodega,
                    IFNULL(P.Id_Punto_Dispensacion, B.Id_Bodega_Nuevo) as Id_Origen_Destino,
                    R.Codigo AS Codigo_Compra
                    FROM Acta_Recepcion_Remision AR
                    LEFT JOIN Bodega_Nuevo B ON AR.Id_Bodega_Nuevo =B.Id_Bodega_Nuevo
                    LEFT JOIN Punto_Dispensacion P ON AR.Id_Punto_Dispensacion = P.Id_Punto_Dispensacion
                    INNER JOIN Remision R ON AR.Id_Remision=R.Id_Remision
                    WHERE AR.Id_Acta_Recepcion_Remision=' . $id_acta;
                $oCon = new consulta();
                $oCon->setQuery($query);
                $datos = $oCon->getData();
                unset($oCon);
                break;
            case 'Ajuste_Individual':
                $query = 'SELECT AR.*, AR.Fecha AS "Fecha_Creacion",
                    "INTERNA" TelefonoProveedor , "INTERNA" DireccionProveedor, "INTERNA" NombreProveedor,
                    Ifnull(B.Nombre, P.Nombre) AS Nombre_Bodega,
                    " INTERNA" AS Codigo_Compra
                    FROM Ajuste_Individual AR
                    Left JOIN Bodega_Nuevo B ON B.Id_Bodega_Nuevo = AR.Id_Origen_Destino and AR.Origen_Destino ="Bodega"
                    Left JOIN Punto_Dispensacion P ON P.Id_Punto_Dispensacion = AR.Id_Origen_Destino and AR.Origen_Destino ="Punto"
                    WHERE AR.Id_Ajuste_Individual=' . $id_acta;
                $oCon = new consulta();
                $oCon->setQuery($query);
                $datos = $oCon->getData();
                unset($oCon);
                break;
            case 'Nota_Credito':
                $query = 'SELECT AR.*, AR.Fecha AS "Fecha_Creacion",
                    "INTERNA" TelefonoProveedor , "INTERNA" DireccionProveedor, "INTERNA" NombreProveedor,
                    B.Nombre AS Nombre_Bodega,
                    B.Id_Bodega_Nuevo as Id_Origen_Destino,
                    "INTERNA" AS Codigo_Compra
                    FROM Nota_Credito AR
                    INNER JOIN Bodega_Nuevo B
                    ON B.Id_Bodega_Nuevo = AR.Id_Bodega_Nuevo
                    WHERE AR.Id_Nota_Credito=' . $id_acta;
                $oCon = new consulta();
                $oCon->setQuery($query);
                $datos = $oCon->getData();
                unset($oCon);
                break;
            case 'Nacionalizacion_Parcial':
                $query = ' SELECT PAI.Id_Nacionalizacion_Parcial , PAI.Codigo, PAI.Fecha_Registro AS "Fecha_Creacion",
                    IFNULL(P.social_reason, CONCAT_WS(" ", P.first_name, P.first_surname)) as NombreProveedor, P.cod_dian_address as DireccionProveedor, P.cell_phone as TelefonoProveedor,
                    B.Nombre AS Nombre_Bodega,
                    B.Id_Bodega_Nuevo as Id_Origen_Destino,
                    OCI.Codigo AS Codigo_Compra
                    FROM Nacionalizacion_Parcial PAI
                    INNER JOIN Acta_Recepcion_Internacional ACI ON ACI.Id_Acta_Recepcion_Internacional =  PAI.Id_Acta_Recepcion_Internacional
                    INNER JOIN Orden_Compra_Internacional OCI ON OCI.Id_Orden_Compra_Internacional = ACI.Id_Orden_Compra_Internacional
                    INNER JOIN third_parties P On P.id = OCI.Id_Proveedor
                    INNER JOIN Bodega_Nuevo B
                    ON B.Id_Bodega_Nuevo = ACI.Id_Bodega_Nuevo
                    WHERE     PAI.Id_Nacionalizacion_Parcial =' . $id_acta;
                $oCon = new consulta();
                $oCon->setQuery($query);
                $datos = $oCon->getData();
                unset($oCon);
                break;
            default:
                break;
        }
        $productos_acta = [];
        $resultado = [];
        $productos_acta = $this->productos_acta($tipo_acta, $id_acta);
        $resultado["Datos"] = $datos;
        $resultado["Datos"]["ConteoProductos"] = count($productos_acta);
        $resultado["Productos"] = $productos_acta;
        return $this->success($resultado);
    }

    public function productos_acta($tipo_acta, $id_acta)
    {
        switch ($tipo_acta) {
            case 'Acta_Recepcion':
                $query2 = 'SELECT P.*, PRD.Nombre_General as Nombre_Producto, Nombre_General, PRD.Referencia,
                IFNULL(POC.Cantidad,0) as Cantidad_Solicitada
                FROM Producto_Acta_Recepcion P
                INNER JOIN Producto PRD
                ON P.Id_Producto=PRD.Id_Producto
                LEFT JOIN Producto_Orden_Compra_Nacional POC
                ON POC.Id_Producto_Orden_Compra_Nacional = P.Id_Producto_Orden_compra
                WHERE P.Id_Acta_Recepcion =' . $id_acta;
                $oCon = new consulta();
                $oCon->setTipo('Multiple');
                $oCon->setQuery($query2);
                $productos_acta = $oCon->getData();
                unset($oCon);
                return $productos_acta;
                break;
            case 'Acta_Recepcion_Remision':
                $query2 = 'SELECT P.*, PRD.Nombre_General as Nombre_Producto,
                IFNULL(P.Cantidad,0) as Cantidad_Solicitada, 0 Precio
                FROM Producto_Acta_Recepcion_Remision P
                INNER JOIN Producto PRD
                ON P.Id_Producto=PRD.Id_Producto
                WHERE P.Id_Acta_Recepcion_Remision =' . $id_acta;
                $oCon = new consulta();
                $oCon->setTipo('Multiple');
                $oCon->setQuery($query2);
                $productos_acta = $oCon->getData();
                unset($oCon);
                return $productos_acta;
                break;
            case 'Ajuste_Individual':
                $query2 = 'SELECT P.*, PRD.Nombre_General as Nombre_Producto,
                IFNULL(P.Cantidad,0) as Cantidad_Solicitada, 0 Precio, E.Nombre AS Estiba, E.Id_Estiba AS Id_Estiba_Ajuste,
                 E.Codigo_Barras AS Codigo_Barras_Estiba_Ajuste
                FROM Producto_Ajuste_Individual P
                INNER JOIN Producto PRD
                ON P.Id_Producto=PRD.Id_Producto
                LEFT JOIN Estiba E
                ON E.Id_Estiba =  P.Id_Nueva_Estiba
                WHERE P.Id_Ajuste_Individual =' . $id_acta;
                $oCon = new consulta();
                $oCon->setTipo('Multiple');
                $oCon->setQuery($query2);
                $productos_acta = $oCon->getData();
                unset($oCon);
                return $productos_acta;
                break;
            case 'Nota_Credito':
                $query2 = 'SELECT P.*, PRD.Nombre_General as Nombre_Producto,
                IFNULL(P.Cantidad,0) as Cantidad_Solicitada, 0 Precio
                FROM Producto_Nota_Credito P
                INNER JOIN Producto PRD
                ON P.Id_Producto=PRD.Id_Producto
                WHERE P.Id_Nota_Credito =' . $id_acta;
                $oCon = new consulta();
                $oCon->setTipo('Multiple');
                $oCon->setQuery($query2);
                $productos_acta = $oCon->getData();
                unset($oCon);
                return $productos_acta;
                break;
            case 'Nacionalizacion_Parcial':
                $query2 = 'SELECT /*PA.*,*/
                        PA.Id_Acta_Recepcion_Internacional, P.Id_Producto,
                            PA.Lote, PA.Fecha_Vencimiento, P.Cantidad,
                        PRD.Nombre_Comercial,
                         PRD.Nombre_General as Nombre_Producto
                FROM Producto_Nacionalizacion_Parcial P
                INNER JOIN Producto_Acta_Recepcion_Internacional PA ON PA.Id_Producto_Acta_Recepcion_Internacional = P.Id_Producto_Acta_Recepcion_Internacional
                INNER JOIN Producto PRD
                ON PRD.Id_Producto = P.Id_Producto
                WHERE P.Id_Nacionalizacion_Parcial =' . $id_acta;
                $oCon = new consulta();
                $oCon->setTipo('Multiple');
                $oCon->setQuery($query2);
                $productos_acta = $oCon->getData();
                unset($oCon);
                return $productos_acta;
                break;
            default:
                break;
        }
    }

    public function validarBodegaInventario(Request $request)
    {
        $id_bodega = $request->input('Id_Bodega_Nuevo', false);

        if ($id_bodega) {
            $query = 'SELECT DOC.Id_Doc_Inventario_Fisico
                FROM Doc_Inventario_Fisico DOC
                INNER JOIN Estiba E ON E.Id_Estiba =  DOC.Id_Estiba
                WHERE DOC.Estado != "Terminado" AND E.Id_Bodega_Nuevo = ' . $id_bodega;
            $oCon = new consulta();
            $oCon->setQuery($query);
            $oCon->setTipo('Multiple');
            $documentos = $oCon->getData();

            if ($documentos) {
                $response['type'] = 'error';
                $response['title'] = '¡No se puede realizar la operación!';
                $response['message'] = 'En este momento la bodega que seleccionó se encuentra realizando un inventario.';
            } else {
                $response['type'] = 'success';
                $response['title'] = 'Bodega Disponible';
                $response['message'] = 'Bodega Disponible';
            }
            return $this->success($response);
        }
    }

    public function validarEstiba(Request $request)
    {
        $codigo_barras = $request->input('codigo_barras', '');
        $tipo = $request->input('tipo', '');
        $Lugar = $request->input('Lugar', '');
        $idLugar = $request->input('idLugar', '');


        $cond = '';
        if ($Lugar == 'Punto_Dispensacion') {
            $cond = ' AND Id_Punto_Dispensacion =' . $idLugar;
        }
        $query = "SELECT * FROM Estiba
        WHERE Codigo_Barras = '$codigo_barras' AND Estado != 'Inactivo' $cond";

        $oCon = new consulta();
        $oCon->setQuery($query);
        $estiba = $oCon->getData();
        unset($oCon);
        $resultado = [];
        // return $estiba;

        if ($estiba) {

            if ($estiba['Estado'] == 'disponible') {
                # code...

                $resultado['Tipo'] = 'success';
                $resultado['Estiba'] = $estiba;
                $resultado['Titulo'] = 'Estiba encontrada';
                $resultado['Mensaje'] = '¡Producto agregado correctamente a la ubicación!';
            } elseif ($estiba['Estado'] == 'inventario') {
                # code...

                $resultado['Tipo'] = 'error';
                $resultado['Titulo'] = 'La ubicación asociada no está permitida';
                $resultado['Mensaje'] = 'Se está realizando un inventario a la ubicación';
            }

        } else {
            $resultado['Tipo'] = 'error';
            $resultado['Titulo'] = 'No se encontró ubicación';
            $resultado['Mensaje'] = 'No existe una ubicación registrada con ese código de barras, por favor verifica.';
        }
        return $this->success($resultado);
    }

    public function acomodarActa(Request $request)
    {
        $id_acta_recepcion = $request->input('id', false);
        $productos = $request->input('productos', false);
        $funcionario = $request->input('funcionario', false);
        $tipo_acta = $request->input('tipo_acta', false);
        $cambio_estiba = $request->input('cambio_estiba', false);
        $bodegaNuevo = $request->input('id_bodega_nuevo_', '');
        $puntoDispensacion = $request->input('id_punto_dispensacion', '');

        $person = People::where('identifier', $funcionario)->first();

        $productos = (array) json_decode($productos, true);

        if ($id_acta_recepcion && $productos && $tipo_acta) {
            //validar si el acta ya fue acomodada

            $actaExistente = $this->validarActa($id_acta_recepcion, $tipo_acta);

            if (!$actaExistente) {
                foreach ($productos as $producto) {
                    //elimar espacios existentes
                    $producto['Lote'] = trim($producto['Lote']);

                    //buscar si el producto está previamente guardado
                    $query = "SELECT Id_Inventario_Nuevo FROM Inventario_Nuevo
                      WHERE Id_Producto=$producto[Id_Producto]
                      AND Lote='$producto[Lote]'
                      # AND Fecha_Vencimiento='$producto[Fecha_Vencimiento]'
                      AND Id_Estiba=$producto[Id_Estiba]";
                    $oCon = new consulta();
                    $oCon->setQuery($query);
                    $inventario = $oCon->getData();
                    unset($oCon);

                    if ($inventario) {
                        //Prev 100
                        $oItem = new complex('Inventario_Nuevo', 'Id_Inventario_Nuevo', $inventario['Id_Inventario_Nuevo']);
                        $cantidad = number_format($producto["Cantidad"], 0, "", "");
                        $cantidad_final = $oItem->Cantidad + $cantidad;
                        $oItem->Cantidad = $cantidad_final;
                        #  $oItem->Costo = $producto['Precio'];
                        $id_inventario = $oItem->Id_Inventario_Nuevo;

                        if (isset($producto['Fecha_Vencimiento']) && $producto['Fecha_Vencimiento'] != '') {
                            $oItem->Fecha_Vencimiento = $producto['Fecha_Vencimiento'];
                        }

                        // 50 => 150
                        //cantDisp = I.Cantidad - (Apartada, Seleccionada) - Contrato(Contrato.Cantidad - (Apartada, Seleccionada))
                        //150-50 = 100

                    } else {
                        $oItem = new complex('Inventario_Nuevo', 'Id_Inventario_Nuevo');
                        $oItem->Codigo = substr(hexdec(uniqid()), 2, 12);
                        $oItem->Cantidad = $producto["Cantidad"];
                        $oItem->Id_Producto = $producto["Id_Producto"];
                        $oItem->Lote = strtoupper($producto["Lote"]);
                        $oItem->Fecha_Vencimiento = $producto["Fecha_Vencimiento"];
                        $oItem->Id_Estiba = $producto["Id_Estiba"];
                        #$oItem->Costo = $producto['Precio'];
                        $oItem->Identificacion_Funcionario = $person->id;
                        $oItem->Id_Bodega = $bodegaNuevo;
                        $oItem->Id_Punto_Dispensacion = $puntoDispensacion;
                    }
                    $oItem->save();
                    $id_inventario = $oItem->getId();
                    unset($oItem);


                    //valida existencia del contrato
                    $validarContrato = $this->validarContrato($id_acta_recepcion);

                    if ($validarContrato && $validarContrato['Id_Contrato']) {
                        $idcontrato = $validarContrato['Id_Contrato'];

                        //buscar si el producto de este contrato fue previamente guardado
                        $query = "SELECT IC.Id_Inventario_Contrato, PC.Id_Producto_Contrato
                                FROM Inventario_Contrato IC
                                INNER JOIN Producto_Contrato PC
                                WHERE IC.Id_Contrato=$idcontrato AND PC.Id_Producto=$producto[Id_Producto]";
                        $oCon = new consulta();
                        $oCon->setQuery($query);
                        $inventariocontrato = $oCon->getData();
                        unset($oCon);

                        if ($inventariocontrato) {
                            $oItem = new complex('Inventario_Contrato', 'Id_Inventario_Contrato', $inventariocontrato['Id_Inventario_Contrato']);
                            $cantidad = number_format($producto["Cantidad"], 0, "", "");
                            $cantidad_final = $oItem->Cantidad + $cantidad;
                            $oItem->Cantidad = $cantidad_final;
                            // $oItem->Id_Inventario_Nuevo = $id_inventario;
                        } else {
                            $oItem = new complex('Inventario_Contrato', 'Id_Inventario_Contrato');
                            $oItem->Id_Contrato = $idcontrato;
                            $oItem->Id_Inventario_Nuevo = $id_inventario;
                            $oItem->Id_Producto_Contrato = $inventariocontrato["Id_Producto_Contrato"];
                            $oItem->Cantidad = $producto["Cantidad"];
                            // $oItem->Cantidad_Apartada = $producto["Fecha_Vencimiento"];
                            // $oItem->Cantidad_Seleccionada = $producto["Id_Estiba"];
                        }
                        $oItem->save();
                        unset($oItem);

                    }


                    if ($tipo_acta == 'Ajuste_Individual') {
                        if (!$cambio_estiba) {
                            # actulizar costos
                            $costopromedio = new CostoPromedio($producto["Id_Producto"], $producto["Cantidad"], $producto["Costo"]);
                            $costopromedio->actualizarCostoPromedio();
                        }
                        #guardar donde se acomodó
                        $oItem = new complex('Producto_Ajuste_Individual', 'Id_Producto_Ajuste_Individual', $producto['Id_Producto_Ajuste_Individual']);
                        $oItem->Id_Estiba_Acomodada = $producto["Id_Estiba"];
                        $oItem->save();
                        unset($oItem);
                    }
                }

                if ($tipo_acta == 'Ajuste_Individual') {
                    # Actividad Ajuste...
                    $this->guardarActividadAD($id_acta_recepcion, $funcionario, 'Se acomodó en las ubicaciones el ajuste individual', 'Acomodada');
                }
                if ($id_inventario) {

                    $this->actualizarActa($id_acta_recepcion, $tipo_acta);
                    if ($tipo_acta == 'Acta_Recepcion') {

                        $this->guardarActividadActa($id_acta_recepcion, $person->id, $tipo_acta);
                    }
                    $resultado['Titulo'] = "Operación exitosa";
                    $resultado['Mensaje'] = "Se ha acomodado e ingresado al inventario correctamente el acta de recepción";
                    $resultado['Tipo'] = "success";

                } else {
                    $resultado['Titulo'] = "Error";
                    $resultado['Mensaje'] = "Ha ocurrido un error inesperado. Por favor inténtalo de nuevo";
                    $resultado['Tipo'] = "error";
                }
            } else {
                $resultado['Titulo'] = "Error acta acomodada";
                $resultado['Mensaje'] = "Ya ha sido acomodada previamente";
                $resultado['Tipo'] = "error";
            }

            return $this->success($resultado);

        } else {
            $resultado['Titulo'] = "Ha ocurrido un error inesperado";
            $resultado['Mensaje'] = "Faltan datos necesarios. Por favor inténtalo de nuevo";
            $resultado['Tipo'] = "error";
            return $this->success($resultado);
        }
    }

    public function validarActa($id_acta_recepcion, $tipo_acta)
    {
        $query = 'SELECT Id_' . $tipo_acta . ' FROM ' . $tipo_acta . ' WHERE Id_' . $tipo_acta . ' = ' . $id_acta_recepcion . ' AND Estado = "Acomodada"';
        $oCon = new consulta();
        $oCon->setQuery($query);
        $acta = $oCon->getData();

        return $acta;
    }

    public function validarContrato($id_acta_recepcion)
    {
        $query = "SELECT PC.Id_Contrato
                    FROM Acta_Recepcion AR
                    INNER JOIN Orden_Compra_Nacional OCN ON AR.Id_Orden_Compra_Nacional = OCN.Id_Orden_Compra_Nacional
                    INNER JOIN Pre_Compra PC ON OCN.Id_Pre_Compra = PC.Id_Pre_Compra
                    WHERE AR.Id_Acta_Recepcion = $id_acta_recepcion ";

        $oCon = new consulta();
        $oCon->setQuery($query);
        $contrato = $oCon->getData();
        unset($oCon);

        return $contrato;
    }

    public function guardarActividadAD($id_ajuste, $funcionario, $detalle, $estado)
    {
        $oItem = new complex('Actividad_Ajuste_Individual', 'Id_Actividad_Ajuste_Individual');
        $oItem->Id_Ajuste_Individual = $id_ajuste;
        $oItem->Identificacion_Funcionario = $funcionario;
        $oItem->Detalle = $detalle;
        $oItem->Estado = $estado;

        $oItem->save();
    }

    public function actualizarActa($id_acta_recepcion, $tipo_acta)
    {

        $oItem = new complex($tipo_acta, 'Id_' . $tipo_acta, $id_acta_recepcion);
        if ($tipo_acta == 'Ajuste_Individual') {
            # code...
            $oItem->Estado_Entrada_Bodega = 'Acomodada';
        } else {
            $oItem->Estado = 'Acomodada';
        }

        $oItem->save();
        unset($oItem);
    }

    public function guardarActividadActa($id_acta_recepcion, $funcionario, $tipo_acta)
    {
        //Consultar el codigo del acta y el id de la orden de compra
        $acta_data = $this->consultarCodigoActa($id_acta_recepcion);
        //Guardando paso en el seguimiento del acta en cuestion
        $oItem = new complex('Actividad_Orden_Compra', "Id_Acta_Recepcion_Compra");
        $oItem->Id_Orden_Compra_Nacional = $acta_data['Id_Orden_Compra_Nacional'];
        $oItem->Id_Acta_Recepcion_Compra = $id_acta_recepcion;
        $oItem->Identificacion_Funcionario = $funcionario;
        $oItem->Detalles = "Se acomodó y se ingreso el Acta con codigo " . $acta_data['Codigo'];
        $oItem->Fecha = date("Y-m-d H:i:s");
        $oItem->Estado = 'Acomodada';
        $oItem->save();
        unset($oItem);

    }

    public function consultarCodigoActa($id_acta_recepcion)
    {
        //Consultar el codigo del acta y el id de la orden de compra

        $query_codido_acta = 'SELECT
        Codigo,
        Id_Orden_Compra_Nacional
        FROM
        Acta_Recepcion
        WHERE
        Id_Acta_Recepcion = ' . $id_acta_recepcion;

        $oCon = new consulta();
        $oCon->setQuery($query_codido_acta);
        $acta_data = $oCon->getData();
        unset($oCon);
        return $acta_data;
    }

    public function listaImpuestoMes2()
    {
        $impuestos = Impuesto::all();
        $meses = CompanyConfiguration::where('company_id', getCompanyWorkedId())->pluck('Expiration_Months');
        $bodegas = BodegaNuevo::select('Id_Bodega_Nuevo', 'Nombre')->where('company_id', getCompanyWorkedId())->get();

        $resultado = [
            'Impuesto' => $impuestos,
            'Meses' => $meses,
            'Bodega' => $bodegas,
        ];

        return response()->json($resultado);
    }

    public function detalleFacturaVenta()
    {
        $id = (isset($_REQUEST['id']) ? $_REQUEST['id'] : '');

        $query = 'SELECT
FV.Fecha_Documento as Fecha, FV.Cufe, FV.Id_Resolucion, FV.Observacion_Factura_Venta as observacion, FV.Codigo as Codigo, FV.Codigo_Qr, IF(FV.Condicion_Pago=1,"CONTADO",FV.Condicion_Pago) as Condicion_Pago , FV.Fecha_Pago as Fecha_Pago ,
C.id as IdCliente ,IFNULL(C.social_reason, CONCAT_WS(" ", C.first_name, C.first_surname)) as NombreCliente, C.dian_address as DireccionCliente, M.name as CiudadCliente, C.assigned_space as CreditoCliente, C.cell_phone AS Telefono, FV.Id_Factura_Venta ,(SELECT R.Observaciones FROM Remision R WHERE Id_Factura = FV.Id_Factura_Venta Order By R.Id_Remision ASC LIMIT 1) as Observaciones2
FROM Factura_Venta FV
INNER JOIN third_parties C ON FV.Id_Cliente = C.id
INNER JOIN municipalities M ON C.municipality_id=M.id
AND FV.Id_Factura_Venta =' . $id;

        $oCon = new consulta();
        $oCon->setQuery($query);
        $dis = $oCon->getData();
        unset($oCon);

        $query2 = 'SELECT
IFNULL(CONCAT(P.Nombre_Comercial, " - ",P.Principio_Activo, " ", P.Cantidad,"", P.Unidad_Medida, " " , P.Presentacion, "\n", P.Invima, " CUM:", P.Codigo_Cum), CONCAT(P.Nombre_Comercial, " LAB-", P.Laboratorio_Comercial)) as producto,
P.Id_Producto,
IF(P.Laboratorio_Generico IS NULL,P.Laboratorio_Comercial,P.Laboratorio_Generico) as Laboratorio,
P.Presentacion,
P.Codigo_Cum as Cum,

(SELECT Costo_Promedio FROM Costo_Promedio WHERE Id_Producto = P.Id_Producto) as CostoUnitario,
 INV.Lote AS Lote,
 INV.Id_Inventario_Nuevo as Id_Inventario,
 INV.Fecha_Vencimiento as Fecha_Vencimiento,

PFV.Precio_Venta as Costo_unitario,
PFV.Cantidad as Cantidad,
PFV.Precio_Venta as PrecioVenta,
(PFV.Cantidad * PFV.Precio_Venta*(1-(PFV.Descuento/100)) ) as Subtotal,
PFV.Id_Producto_Factura_Venta as idPFV,
(CASE
  WHEN P.Gravado = "Si" AND C.apply_iva="Si" THEN "19%"
  ELSE "0%"
END) as Impuesto,
CONCAT(PFV.Impuesto,"%") as Impuesto
FROM Producto_Factura_Venta PFV
LEFT JOIN Inventario_Nuevo INV
ON PFV.Id_Inventario_Nuevo = INV.Id_Inventario_Nuevo
LEFT JOIN Producto P ON PFV.Id_Producto = P.Id_Producto
INNER JOIN Factura_Venta F
ON PFV.Id_Factura_Venta=F.Id_Factura_Venta
INNER JOIN third_parties C
ON F.Id_Cliente=C.id
WHERE PFV.Id_Factura_Venta =' . $id;

        $oCon = new consulta();
        $oCon->setQuery($query2);
        $oCon->setTipo('Multiple');
        $productos = $oCon->getData();
        unset($oCon);

        if (count($productos) == 0) {
            $query22 = 'SELECT
    IFNULL(CONCAT(P.Principio_Activo, " ", P.Cantidad,"", P.Unidad_Medida, " " , P.Presentacion, "\n", P.Invima, " CUM:", P.Codigo_Cum), CONCAT(P.Nombre_Comercial, " LAB-", P.Laboratorio_Comercial)) as producto,
    P.Id_Producto,
    IF(P.Laboratorio_Generico IS NULL,P.Laboratorio_Comercial,P.Laboratorio_Generico) as Laboratorio,
    P.Presentacion,
    P.Codigo_Cum as Cum,
    PFV.Fecha_Vencimiento as Vencimiento,
    PFV.Lote as Lote,
    PFV.Id_Inventario as Id_Inventario,
    PFV.Precio_Venta as Costo_unitario,
    PFV.Cantidad as Cantidad,
    PFV.Precio_Venta as PrecioVenta,
    (PFV.Cantidad * PFV.Precio_Venta*(1-(PFV.Descuento/100)) ) as Subtotal,
    PFV.Id_Producto_Factura_Venta as idPFV,
    (CASE
      WHEN P.Gravado = "Si" AND C.apply_iva="Si" THEN "19%"
      ELSE "0%"
    END) as Impuesto,
    CONCAT(PFV.Impuesto,"%") as Impuesto
    FROM Producto_Factura_Venta PFV
    LEFT JOIN Producto P ON PFV.Id_Producto = P.Id_Producto
    INNER JOIN Factura_Venta F
    ON PFV.Id_Factura_Venta=F.Id_Factura_Venta
    INNER JOIN third_parties C
    ON F.Id_Cliente=C.Id_Cliente
    WHERE PFV.Id_Factura_Venta =' . $id;

            $oCon = new consulta();
            $oCon->setQuery($query22);
            $oCon->setTipo('Multiple');
            $productos = $oCon->getData();
            unset($oCon);
        }


        /*
        $query3 = 'SELECT * FROM `Nota_Credito` WHERE `Id_Factura` =  '.$id ;

        $oCon= new consulta();
        $oCon->setQuery($query3);
        $oCon->setTipo('Multiple');
        $notasCredito = $oCon->getData();
        unset($oCon);
        */

        // total para la suma de las notas credito
        $query4 = 'SELECT SUM(Descuento_Factura) as TotalNC FROM Nota_Credito WHERE Id_Factura = ' . $id;
        $oCon = new consulta();
        $oCon->setQuery($query4);
        // $totalNotasCredito = $oCon->getData();
        $totalNotasCredito = ["TotalNC" => 0];
        unset($oCon);

        // consulta para total de facturas

        $query5 = 'SELECT SUM((Cantidad * Precio_Venta*(1-(Descuento/100)) )) as TotalFac FROM Producto_Factura_Venta WHERE Id_Factura_Venta = ' . $id;
        $oCon = new consulta();
        $oCon->setQuery($query5);
        $totalFactura = $oCon->getData();
        unset($oCon);


        $total_impuesto = 0;
        foreach ($productos as $prod) {
            $total_impuesto += ($prod->Subtotal * (intval(str_replace("%", "", $prod->Impuesto)) / 100));
        }

        $totalFactura["Iva"] = $total_impuesto;
        $total = $totalFactura['TotalFac'] + $total_impuesto;
        $numero = number_format($total, 2, '.', '');

        $letras = NumeroALetras::convertir($numero) . " PESOS MCTE.";

        $oItem = new complex('Remision', 'Id_Factura', $id);
        $id_remision = $oItem->Id_Remision;
        unset($oItem);

        $actividades = [];

        if ($id_remision) {

            $query = 'SELECT AR.*, F.image,F.full_name as Funcionario,
  (CASE
      WHEN AR.Estado="Creacion" THEN CONCAT("1 ",AR.Estado)
      WHEN AR.Estado="Alistamiento" THEN CONCAT("2 ",AR.Estado)
      WHEN AR.Estado="Edicion" THEN CONCAT("2 ",AR.Estado)
      WHEN AR.Estado="Fase 1" THEN CONCAT("2 ",AR.Estado)
      WHEN AR.Estado="Fase 2" THEN CONCAT("3 ",AR.Estado)
      WHEN AR.Estado="Enviada" THEN CONCAT("4 ",AR.Estado)
      WHEN AR.Estado="Facturada" THEN CONCAT("5 ",AR.Estado)
      WHEN AR.Estado="Recibida" THEN CONCAT("5 ",AR.Estado)
      WHEN AR.Estado="Anulada" THEN CONCAT("2 ",AR.Estado)
  END) as Estado2
  FROM Actividad_Remision AR
  INNER JOIN people F
  On AR.Identificacion_Funcionario=F.id
  WHERE AR.Id_Remision=' . $id_remision . ' AND AR.Estado IN ("Facturada","Anulada")
  Order BY Estado2 ASC, Fecha ASC';

            $oCon = new consulta();
            $oCon->setQuery($query);
            $oCon->setTipo('Multiple');
            $actividades = $oCon->getData();
            unset($oCon);


            $query = "SELECT '' as Id_Actividad_Remision, '' as Id_Remision, NC.Identificacion_Funcionario, NC.Fecha, CONCAT('Se realizo un Nota credito de la factura, el codigo de esta nota es ',NC.Codigo) as Detalles, 'Creacion' as Estado, F.image,F.full_name as Funcionario, '' as Estado2  FROM Nota_Credito NC INNER JOIN people F ON NC.Identificacion_Funcionario=F.id WHERE NC.Id_Factura=" . $id;
            $oCon = new consulta();
            $oCon->setQuery($query);
            $oCon->setTipo('Multiple');
            $actividades_nota = $oCon->getData();
            unset($oCon);

            $actividades = array_merge($actividades, $actividades_nota);
        }

        $oItem = new complex("Resolucion", "Id_Resolucion", $dis['Id_Resolucion']);
        $resolucion = $oItem->getData();
        unset($oItem);


        $resultado["Datos"] = $dis;
        $resultado["actividades"] = $actividades;
        $resultado["Productos"] = $productos;
        $resultado["NotasCredito"] = [];
        $resultado["TotalNc"] = $totalNotasCredito;
        $resultado["TotalFc"] = $totalFactura;
        $resultado["letra"] = $letras;
        $resultado["resolucion"] = $resolucion;

        return $this->success($resultado);
    }

    public function descargaPdf(Request $request)
    {
        $id = $request->input('id', '');

        /* DATOS DEL ARCHIVO A MOSTRAR */
        $oItem = new complex("Factura_Venta", "Id_Factura_Venta", $id);
        $data = $oItem->getData();
        unset($oItem);

        $query = "SELECT * FROM Resolucion WHERE Id_Resolucion=" . $data["Id_Resolucion"];

        $oCon = new consulta();
        $oCon->setQuery($query);
        $fact = $oCon->getData();
        unset($oCon);

        $query = 'SELECT
        FV.Fecha_Documento as Fecha, FV.Cufe, FV.Observacion_Factura_Venta as observacion,
        FV.Codigo as Codigo, FV.Codigo_Qr, IF(FV.Condicion_Pago=1,"CONTADO",FV.Condicion_Pago) as Condicion_Pago ,
        FV.Fecha_Pago as Fecha_Pago ,
        C.id as IdCliente ,IFNULL(C.social_reason, CONCAT_WS(" ", C.first_name, C.first_surname))  as NombreCliente, C.dian_address as DireccionCliente,
        M.name as CiudadCliente, C.assigned_space as CreditoCliente, C.cell_phone AS Telefono, FV.Id_Factura_Venta,
        (SELECT R.Observaciones FROM Remision R WHERE Id_Factura = FV.Id_Factura_Venta Order By R.Id_Remision ASC LIMIT 1) as Observaciones2
        FROM Factura_Venta FV
        INNER JOIN third_parties C ON FV.Id_Cliente = C.id
        INNER JOIN municipalities M ON C.municipality_id=M.id
        AND FV.Id_Factura_Venta =' . $id;

        $oCon = new consulta();
        $oCon->setQuery($query);
        $cliente = $oCon->getData();
        unset($oCon);




        $query = 'SELECT
        P.Nombre_General as producto,
        P.Id_Producto,
        PFV.Fecha_Vencimiento as Vencimiento,
        PFV.Lote as Lote,
        IFNULL(PFV.Id_Inventario,PFV.Id_Inventario_Nuevo) as Id_Inventario,
        PFV.Precio_Venta as Costo_unitario,
        PFV.Cantidad as Cantidad,
        PFV.Precio_Venta as PrecioVenta,
        PFV.Subtotal as Subtotal,
        Regimen.Nombre as Regimen,
        PFV.Id_Producto_Factura_Venta as idPFV,
        (CASE
        WHEN P.Gravado = "Si" AND C.apply_iva="Si" THEN "19%"
        ELSE "0%"
        END) as Impuesto,
        CONCAT(PFV.Impuesto,"%") as Impuesto
        FROM Producto_Factura_Venta PFV

        INNER JOIN Factura_Venta F
        ON PFV.Id_Factura_Venta=F.Id_Factura_Venta
        INNER JOIN third_parties C
        ON F.Id_Cliente=C.id
        LEFT JOIN Regimen
        ON Regimen.Id_Regimen = C.regime
        LEFT JOIN Producto P ON PFV.Id_Producto = P.Id_Producto
        WHERE PFV.Id_Factura_Venta =' . $id;

        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->setTipo("Multiple");
        $productos = $oCon->getData();
        unset($oCon);

        $regimen = '';
        if ($productos[0]->Regimen == 'Comun') {
            $regimen = 'Impuesto Sobre las Ventas-IVA';
        } elseif ($productos[0]->Regimen == 'Simplificado') {
            $regimen = 'No Responsable IVA';

        }


        if (count($productos) == 0) {
            $query22 = 'SELECT
    P.Nombre_General as producto,
    P.Id_Producto,
    PFV.Fecha_Vencimiento as Vencimiento,
    PFV.Lote as Lote,
    IFNULL(PFV.Id_Inventario,PFV.Id_Inventario_Nuevo)as Id_Inventario,
    PFV.Precio_Venta as Costo_unitario,
    PFV.Cantidad as Cantidad,
    PFV.Precio_Venta as PrecioVenta,
    PFV.Subtotal as Subtotal,
    PFV.Descuento as Descuento,
    PFV.Id_Producto_Factura_Venta as idPFV,
    (CASE
      WHEN P.Gravado = "Si" THEN "19%"
      ELSE "0%"
    END) as Impuesto,
    CONCAT(PFV.Impuesto,"%") as Impuesto
    FROM Producto_Factura_Venta PFV
    LEFT JOIN Producto P ON PFV.Id_Producto = P.Id_Producto
    WHERE PFV.Id_Factura_Venta =' . $id;

            $oCon = new consulta();
            $oCon->setQuery($query22);
            $oCon->setTipo('Multiple');
            $productos = $oCon->getData();
            unset($oCon);
        }

        $elabora = Person::where('id', $data["Id_Funcionario"])
            ->value(DB::raw("IFNULL(CONCAT_WS(' ', first_name, first_surname), 'Nombre no disponible')"));

        $tipo = "Factura";

        $query5 = 'SELECT SUM(Subtotal) as TotalFac FROM Producto_Factura_Venta WHERE Id_Factura_Venta = ' . $id;
        $oCon = new consulta();
        $oCon->setQuery($query5);
        $totalFactura = $oCon->getData();

        if ($fact["Tipo_Resolucion"] == "Resolucion_Electronica") {
            $titulo = "Factura Electrónica de Venta";
        } else {
            $titulo = "Factura de Venta";
        }

        $condicion_pago = $cliente["Condicion_Pago"] == "CONTADO" ? $cliente["Condicion_Pago"] : "Credito a $cliente[Condicion_Pago] Días";

        $header = (object) [
            'titulo' => $titulo,
            'Codigo' => $data['Codigo'] ?? '',
            'Fecha_Documento' => $data['Fecha_Documento'],
            'Fecha_Pago' => $data['Fecha_Pago'],
            'CodigoFormato' => $data['Codigo'] ?? '',
            'regimen' => $regimen,
            'condicion_pago' => $condicion_pago,
        ];
        $letras = new NumeroALetras();

        $pdf = Pdf::loadView('pdf.facturasventas', [
            // 'movimientos_suma' => $movimientos_suma,
            // 'movimientos' => $movimientos,
            // 'imprime' => $imprime,
            'datosCabecera' => $header,
            'elabora' => $elabora,
            'data' => $data,
            'fact' => $fact,
            'cliente' => $cliente,
            'letras' => $letras,
            'productos' => $productos,
            // 'tipo_valor' => $tipo_valor

        ]);

        return $pdf->stream("facturasventas");

    }

    public function descargarPdfActaRemision()
    {
        $tipo = (isset($_REQUEST['tipo']) ? $_REQUEST['tipo'] : '');
        $id = (isset($_REQUEST['id']) ? $_REQUEST['id'] : '');
        $titulo = $tipo != '' ? 'Acta Recepcion Remision Bodegas' : 'Acta Recepcion Remision';

        /* DATOS DEL ARCHIVO A MOSTRAR */
        $oItem = new complex($tipo, "Id_" . $tipo, $id);
        $data = $oItem->getData();
        unset($oItem);

        $query = '';

        if (isset($_REQUEST['Acta_Tipo']) && $_REQUEST['Acta_Tipo'] == 'Bodega') {
            $query = 'SELECT AR.*, (SELECT Nombre FROM Bodega_Nuevo WHERE Id_Bodega_Nuevo=AR.Id_Bodega_Nuevo) as Nombre_Bodega, R.Codigo as Codigo_Remision, R.Nombre_Origen
            FROM Acta_Recepcion_Remision AR
            INNER JOIN Remision R
            ON AR.ID_Remision=R.Id_Remision
            WHERE AR.Id_Acta_Recepcion_Remision=' . $id;

        } else {
            $query = 'SELECT AR.*,
                ifnull(P.Nombre, B.Nombre )as Nombre_Punto, R.Codigo as Codigo_Remision, R.Nombre_Origen, CONCAT(F.first_name, " ", F.first_surname) as Elabora
            FROM Acta_Recepcion_Remision AR
            LEFT JOIN Punto_Dispensacion P ON AR.Id_Punto_Dispensacion=P.Id_Punto_Dispensacion
            LEFT JOIN Bodega_Nuevo B ON AR.Id_Bodega_Nuevo=B.Id_Bodega_Nuevo
            INNER JOIN Remision R ON AR.ID_Remision=R.Id_Remision
            INNER JOIN people F ON R.Identificacion_Funcionario=F.id
            WHERE AR.Id_Acta_Recepcion_Remision=' . $id;
        }

        $oCon = new consulta();
        //$oCon->setTipo('Multiple');
        $oCon->setQuery($query);
        $datos = $oCon->getData();
        unset($oCon);

        $query2 = 'SELECT P.*, IFNULL(CONCAT( PRD.Principio_Activo, " ",
        PRD.Presentacion, " ",
        PRD.Concentracion, " (", PRD.Nombre_Comercial,") ",
        PRD.Cantidad," ",
        PRD.Unidad_Medida, " " ), CONCAT(PRD.Nombre_Comercial," LAB-", PRD.Laboratorio_Comercial)) AS Nombre_Producto, PRD.Embalaje, PRD.Invima, CONCAT_WS(" / ", PRD.Laboratorio_Comercial, PRD.Laboratorio_Generico) AS Laboratorios
        FROM Producto_Acta_Recepcion_Remision P
        INNER JOIN Producto PRD
        ON P.Id_Producto=PRD.Id_Producto
        WHERE P.Id_Acta_Recepcion_Remision=' . $id;

        $oCon = new consulta();
        $oCon->setTipo('Multiple');
        $oCon->setQuery($query2);
        $productos = $oCon->getData();
        unset($oCon);

        $titulo_punto_bodega = isset($_REQUEST['Acta_Tipo']) && $_REQUEST['Acta_Tipo'] == 'Bodega' ? 'Bodega Destino' : 'Punto Destino';
        $punto_bodega = isset($_REQUEST['Acta_Tipo']) && $_REQUEST['Acta_Tipo'] == 'Bodega' ? $datos['Nombre_Bodega'] : $datos['Nombre_Punto'];

        $oItem = new complex('people', "identifier", $data["Identificacion_Funcionario"]);
        $recibe = $oItem->getData();
        unset($oItem);


        $fecha = $data['Fecha'];

        $header = (object) [
            'Titulo' => $titulo,
            'Codigo' => $data["Codigo"] ?? '',
            'Fecha' => $fecha,
            'CodigoFormato' => $data->format_code ?? '',
        ];

        $pdf = Pdf::loadView('pdf.descarga_pdf_acta_remision', [
            'datos' => $datos,
            'datosCabecera' => $header,
            'titulo_punto_bodega' => $titulo_punto_bodega,
            'punto_bodega' => $punto_bodega,
            'recibe' => $recibe,
            'productos' => $productos
        ]);

        return $pdf->stream("descarga_pdf_acta_remision");

    }

    public function guardarActaRemisionesPendientes()
    {
        $http_response = new HttpResponse();
        $response = array();

        $modelo = (isset($_REQUEST['modelo']) ? $_REQUEST['modelo'] : '');
        $datos = (isset($_REQUEST['datos']) ? $_REQUEST['datos'] : '');

        $modelo = json_decode($modelo, true);
        $datos = json_decode($datos, true);


        $query = 'SELECT PD.Fecha FROM Producto_Descarga_Pendiente_Remision PD WHERE PD.Id_Remision =' . $datos['Id_Remision'] . ' ORDER BY PD.Id_Producto_Descarga_Pendiente_Remision DESC LIMIT 1';
        $oCon = new consulta();
        $oCon->setQuery($query);
        $fecha = $oCon->getData();
        unset($oCon);

        $cod = getConsecutive('Acta_Recepcion_Remision');
        sumConsecutive('Acta_Recepcion_Remision');

        $oItem = new complex('Remision', 'Id_Remision', $datos["Id_Remision"]);
        $oItem->Estado = "Recibida";
        $oItem->save();
        $remision = $oItem->getData();
        unset($oItem);

        $datos['Id_Punto_Dispensacion'] = $remision['Id_Destino'];
        $datos['Entrega_Pendientes'] = $remision['Entrega_Pendientes'];


        $datos['Codigo'] = $cod;
        $datos['Fecha'] = $fecha['Fecha'] ?? '';
        $oItem = new complex("Acta_Recepcion_Remision", "Id_Acta_Recepcion_Remision");

        foreach ($datos as $index => $value) {
            $oItem->$index = $value;
        }
        $oItem->save();
        $id_Acta_Recepcion_remision = $oItem->getId();
        unset($oItem);

        /* AQUI GENERA QR */
        $qr = generarqr('actarecepcionremision', $id_Acta_Recepcion_remision, '/IMAGENES/QR/');
        $oItem = new complex("Acta_Recepcion_Remision", "Id_Acta_Recepcion_Remision", $id_Acta_Recepcion_remision);
        $oItem->Codigo_Qr = $qr;
        $oItem->save();
        unset($oItem);
        /* HASTA AQUI GENERA QR */


        $query = 'SELECT PR.*, P.Codigo_Cum FROM Producto_Remision PR INNER JOIN Producto P ON PR.Id_Producto=P.Id_Producto WHERE PR.Id_Remision =' . $datos['Id_Remision'];


        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->setTipo('Multiple');
        $productos = $oCon->getData();
        unset($oCon);

        foreach ($productos as $item) {
            $queryInsert[] = "($item->Id_Producto,'$item->Lote','$item->Fecha_Vencimiento',$item->Cantidad,$item->Id_Remision,$item->Id_Producto_Remision,$id_Acta_Recepcion_remision)";
        }
        if (count($queryInsert) > 0) {
            $this->registrarSaldos($queryInsert);
        }

        foreach ($modelo as $item) {
            $query = 'SELECT I.Id_Inventario_Nuevo
    FROM Inventario_Nuevo I
    WHERE I.Id_Punto_Dispensacion=' . $datos['Id_Punto_Dispensacion'] . ' AND I.Id_Producto=' . $item['Id_Producto'] . ' AND  I.Lote="' . $item['Lote'] . '"';
            $oCon = new consulta();
            $oCon->setQuery($query);
            $inventario = $oCon->getData();
            unset($oCon);
            $queryInsertInventario = [];

            if ($inventario) {
                $query2 = "UPDATE Inventario_Nuevo SET  Cantidad=(Cantidad+$item[Cantidad]), Cantidad_Pendientes=(Cantidad_Pendientes-$item[Cantidad]) WHERE Id_Inventario_Nuevo=$inventario[Id_Inventario_Nuevo]";

                $oCon = new consulta();
                $oCon->setQuery($query2);
                $oCon->createData();
                unset($oCon);
            } else {

                $fecha = date("Y-m-d H:i:s");
                $queryInsertInventario[] = "($item[Id_Producto],'$item[Lote]',$item[Cantidad],'$item[Precio]','$item[Fecha_Vencimiento]',0,$datos[Id_Punto_Dispensacion],'$item[Codigo_Cum]',$datos[Identificacion_Funcionario],'$fecha' )";

            }

            $query = 'SELECT PAR.Id_Producto_Acta_Recepcion_Remision
    FROM Producto_Acta_Recepcion_Remision PAR
    WHERE PAR.Id_Acta_Recepcion_Remision=' . $id_Acta_Recepcion_remision . ' AND PAR.Id_Producto_Remision=' . $item['Id_Producto_Remision'];

            $oCon = new consulta();
            $oCon->setQuery($query);
            $productoacta = $oCon->getData();
            unset($oCon);
            if ($productoacta) {
                if (isset($item['Temperatura']) && $item['Temperatura'] != '') {
                    $query2 = "UPDATE Producto_Acta_Recepcion_Remision SET  Cumple='$item[Cumple]', Revisado='$item[Revisado]',Temperatura='$item[Temperatura]' WHERE Id_Producto_Acta_Recepcion_Remision=$productoacta[Id_Producto_Acta_Recepcion_Remision]";

                    $oCon = new consulta();
                    $oCon->setQuery($query2);
                    $oCon->createData();
                    unset($oCon);
                } else {
                    $query2 = "UPDATE Producto_Acta_Recepcion_Remision SET  Cumple='$item[Cumple]', Revisado='$item[Revisado]' WHERE Id_Producto_Acta_Recepcion_Remision=$productoacta[Id_Producto_Acta_Recepcion_Remision]";
                    $oCon = new consulta();
                    $oCon->setQuery($query2);
                    $oCon->createData();
                    unset($oCon);
                }


            }
        }
        if (count($queryInsertInventario) > 0) {
            $this->registrarInventario($queryInsertInventario);
        }

        $this->GuardarActividadRemision($datos, $remision);
        //GuardarAlerta($modelo['Id_Auditoria']);

        $http_response->SetRespuesta(0, 'Registro Exitoso', 'Se ha(n) agregado(s) exitosamente todos los productos !');
        $response = $http_response->GetRespuesta();

        return $this->success($response);

    }

    private function registrarSaldos($queryInsert)
    {
        $query = "INSERT INTO Producto_Acta_Recepcion_Remision (Id_Producto,Lote,Fecha_Vencimiento,Cantidad,Id_Remision,Id_Producto_Remision,Id_Acta_Recepcion_Remision) VALUES " . implode(',', $queryInsert);

        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->createData();
        unset($oCon);

        return;
    }

    private function registrarInventario($queryInsertInventario)
    {
        $query = "INSERT INTO Inventario_Nuevo (Id_Producto,Lote,Cantidad_Pendientes,Costo,Fecha_Vencimiento,Id_Bodega,Id_Punto_Dispensacion,Codigo_CUM,Identificacion_Funcionario,Fecha_Carga) VALUES " . implode(',', $queryInsertInventario);

        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->createData();
        unset($oCon);

        return;
    }

    private function GuardarActividadRemision($datos, $remision)
    {
        $oItem = new complex('Actividad_Remision', "Id_Actividad_Remision");
        $oItem->Id_Remision = $datos["Id_Remision"];
        $oItem->Identificacion_Funcionario = $datos['Identificacion_Funcionario'];
        $oItem->Detalles = "Se hace el acta de recepcion de la  " . $remision["Codigo"];
        $oItem->Estado = "Recibida";
        $oItem->save();
        unset($oItem);

    }

    private function inventariarProductos($id_acta_recepcion, $bodegaNuevo, $puntoDispensacion)
    {
        $tipo_acta = 'Acta_Recepcion';
        $productos = ProductoActaRecepcion::where('Id_Acta_Recepcion', $id_acta_recepcion)->get();
        if ($id_acta_recepcion && $productos && $tipo_acta) {
            //validar si el acta ya fue acomodada

            $actaExistente = $this->validarActa($id_acta_recepcion, $tipo_acta);

            if (!$actaExistente) {
                foreach ($productos as $producto) {
                    //elimar espacios existentes
                    $producto->Lote = trim($producto->Lote);

                    //buscar si el producto está previamente guardado
                    $query = "SELECT Id_Inventario_Nuevo FROM Inventario_Nuevo
                      WHERE Id_Producto=$producto->Id_Producto
                      AND Lote='$producto->Lote'
                      # AND Fecha_Vencimiento='$producto->Fecha_Vencimiento'";
                    $oCon = new consulta();
                    $oCon->setQuery($query);
                    $inventario = $oCon->getData();
                    unset($oCon);

                    if ($inventario) {
                        //Prev 100
                        $oItem = new complex('Inventario_Nuevo', 'Id_Inventario_Nuevo', $inventario['Id_Inventario_Nuevo']);
                        $cantidad = number_format($producto->Cantidad, 0, "", "");
                        $cantidad_final = $oItem->Cantidad + $cantidad;
                        $oItem->Cantidad = $cantidad_final;
                        #  $oItem->Costo = $producto['Precio'];
                        $id_inventario = $oItem->Id_Inventario_Nuevo;

                        if (isset($producto->Fecha_Vencimiento) && $producto->Fecha_Vencimiento != '') {
                            $oItem->Fecha_Vencimiento = $producto->Fecha_Vencimiento;
                        }

                        // 50 => 150
                        //cantDisp = I.Cantidad - (Apartada, Seleccionada) - Contrato(Contrato.Cantidad - (Apartada, Seleccionada))
                        //150-50 = 100

                    } else {
                        $oItem = new complex('Inventario_Nuevo', 'Id_Inventario_Nuevo');
                        $oItem->Codigo = substr(hexdec(uniqid()), 2, 12);
                        $oItem->Cantidad = $producto->Cantidad;
                        $oItem->Id_Producto = $producto->Id_Producto;
                        $oItem->Lote = strtoupper($producto->Lote);
                        $oItem->Fecha_Vencimiento = $producto->Fecha_Vencimiento;
                        // $oItem->Id_Estiba = $producto->Id_Estiba;
                        $oItem->Identificacion_Funcionario = Person::find(Auth()->user()->person_id)->id;
                        $oItem->Id_Bodega = $bodegaNuevo;
                        $oItem->Id_Punto_Dispensacion = $puntoDispensacion;
                    }
                    $oItem->save();
                    $id_inventario = $oItem->getId();
                    unset($oItem);


                    //valida existencia del contrato
                    $validarContrato = $this->validarContrato($id_acta_recepcion);

                    if ($validarContrato && $validarContrato['Id_Contrato']) {
                        $idcontrato = $validarContrato['Id_Contrato'];

                        //buscar si el producto de este contrato fue previamente guardado
                        $query = "SELECT IC.Id_Inventario_Contrato, PC.Id_Producto_Contrato
                                FROM Inventario_Contrato IC
                                INNER JOIN Producto_Contrato PC
                                WHERE IC.Id_Contrato=$idcontrato AND PC.Id_Producto=$producto[Id_Producto]";
                        $oCon = new consulta();
                        $oCon->setQuery($query);
                        $inventariocontrato = $oCon->getData();
                        unset($oCon);

                        if ($inventariocontrato) {
                            $oItem = new complex('Inventario_Contrato', 'Id_Inventario_Contrato', $inventariocontrato['Id_Inventario_Contrato']);
                            $cantidad = number_format($producto["Cantidad"], 0, "", "");
                            $cantidad_final = $oItem->Cantidad + $cantidad;
                            $oItem->Cantidad = $cantidad_final;
                            // $oItem->Id_Inventario_Nuevo = $id_inventario;
                        } else {
                            $oItem = new complex('Inventario_Contrato', 'Id_Inventario_Contrato');
                            $oItem->Id_Contrato = $idcontrato;
                            $oItem->Id_Inventario_Nuevo = $id_inventario;
                            $oItem->Id_Producto_Contrato = $inventariocontrato["Id_Producto_Contrato"];
                            $oItem->Cantidad = $producto["Cantidad"];
                            // $oItem->Cantidad_Apartada = $producto["Fecha_Vencimiento"];
                            // $oItem->Cantidad_Seleccionada = $producto["Id_Estiba"];
                        }
                        $oItem->save();
                        unset($oItem);

                    }
                }

                if ($id_inventario) {

                    $this->actualizarActa($id_acta_recepcion, $tipo_acta);
                    if ($tipo_acta == 'Acta_Recepcion') {

                        $this->guardarActividadActa($id_acta_recepcion, Person::find(Auth()->user()->person_id)->id, $tipo_acta);
                    }
                    $resultado['Titulo'] = "Operación exitosa";
                    $resultado['Mensaje'] = "Se ha acomodado e ingresado al inventario correctamente el acta de recepción";
                    $resultado['Tipo'] = "success";

                } else {
                    $resultado['Titulo'] = "Error";
                    $resultado['Mensaje'] = "Ha ocurrido un error inesperado. Por favor inténtalo de nuevo";
                    $resultado['Tipo'] = "error";
                }
            } else {
                $resultado['Titulo'] = "Error acta acomodada";
                $resultado['Mensaje'] = "Ya ha sido acomodada previamente";
                $resultado['Tipo'] = "error";
            }

            return $this->success($resultado);

        } else {
            $resultado['Titulo'] = "Ha ocurrido un error inesperado";
            $resultado['Mensaje'] = "Faltan datos necesarios. Por favor inténtalo de nuevo";
            $resultado['Tipo'] = "error";
            return $this->success($resultado);
        }
    }

}
