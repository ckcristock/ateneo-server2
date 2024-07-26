<?php

namespace App\Http\Controllers;

use App\Http\Services\complex;
use App\Http\Services\consulta;
use App\Http\Services\Contabilizar;
use App\Http\Services\HttpResponse;
use App\Http\Services\QueryBaseDatos;
use App\Models\AjusteIndividual;
use App\Models\BodegaNuevo;
use App\Models\ClaseAjusteIndividual;
use App\Models\InventarioNuevo;
use App\Models\Person;
use App\Models\ProductoAjusteIndividual;
use App\Models\PuntoDispensacion;
use App\Traits\ApiResponser;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use stdClass;

class AjusteIndividualController extends Controller
{
    use ApiResponser;
    public function listaAjusteIndividual()
    {

        $ajustes = AjusteIndividual::select('Ajuste_Individual.*')
            ->selectRaw("F.full_name AS Funcionario")
            ->selectRaw("IF(Ajuste_Individual.Origen_Destino = 'Bodega', (SELECT B.Nombre FROM Bodega_Nuevo B WHERE B.Id_Bodega_Nuevo=Ajuste_Individual.Id_Origen_Destino), (SELECT B.Nombre FROM Punto_Dispensacion B WHERE B.Id_Punto_Dispensacion=Ajuste_Individual.Id_Origen_Destino)) as Bodega")
            ->selectRaw("IFNULL((SELECT ROUND(SUM(Cantidad*Costo)) FROM Producto_Ajuste_Individual WHERE Id_Ajuste_Individual = Ajuste_Individual.Id_Ajuste_Individual), 0) AS Valor_Ajuste")
            ->join('people as F', 'F.identifier', '=', 'Ajuste_Individual.Identificacion_Funcionario');

        if (request('cod')) {
            $ajustes->where('Ajuste_Individual.Codigo', 'like', '%' . request('cod') . '%');
        }



        if (request('tipo')) {
            $ajustes->where('Ajuste_Individual.Tipo', 'like', '%' . request('tipo') . '%');
        }

        if (request('fecha')) {
            $fechas = explode(' - ', request('fecha'));
            $fecha_inicio = trim($fechas[0]);
            $fecha_fin = trim($fechas[1]);
            $ajustes->whereBetween('Ajuste_Individual.Fecha', [$fecha_inicio, $fecha_fin]);
        }

        if (request('fun')) {
            $ajustes->where(function ($query) {
                $query->where('F.full_name', 'like', '%' . request('fun') . '%');
            });
        }

        if (request('bod')) {
            $ajustes->having(DB::raw('LOWER(Bodega)'), 'like', '%' . strtolower(request('bod')) . '%');
        }

        $ajustes->orderByDesc('Ajuste_Individual.Id_Ajuste_Individual');
        $ajustes = $ajustes->paginate(Request()->get('pageSize', 10), ['*'], 'page', Request()->get('page', 1));


        return $this->success($ajustes);

    }

    public function claseAjusteIndividual()
    {
        $entradas = ClaseAjusteIndividual::whereIn('Tipo', ['General', 'Entrada'])->get();
        $salidas = ClaseAjusteIndividual::whereIn('Tipo', ['General', 'Salida'])->get();

        $resultado['entradas'] = $entradas;
        $resultado['salidas'] = $salidas;

        return response()->json($resultado);
    }

    public function consultarBodegaPunto(Request $request)
    {
        $tipo = $request->input('tipo', '');

        if (empty($tipo)) {
            return response()->json(['error' => 'El tipo para realizar la consulta está vacío'], 400);
        }

        $resultados = [];

        if ($tipo == 'Bodega_Nuevo') {
            $resultados = BodegaNuevo::select('Id_Bodega_Nuevo as Id_Bodega_Punto', 'Nombre as Nombre_Bodega_Punto')
                ->orderBy('Nombre', 'ASC')
                ->get();
        } else {
            $resultados = PuntoDispensacion::select('Id_Punto_Dispensacion as Id_Bodega_Punto', 'Nombre as Nombre_Bodega_Punto')
                ->orderBy('Nombre', 'ASC')
                ->get();
        }

        return response()->json($resultados);

    }

    public function listaProductosEntrada(Request $request)
    {
        $term = $request->query('name'); // Obtener el término de búsqueda de la consulta
    
        $productos = DB::table('Producto as PRD')
            ->select(
                'PRD.Id_Producto',
                'PRD.Nombre_General as Nombre',
                'PRD.Nombre_Comercial as Nombre_Producto',
                DB::raw('"" AS Lote'),
                DB::raw('"" AS Cantidad'),
                DB::raw('IFNULL(CP.Costo_Promedio, 0) AS Costo'),
                DB::raw('"" AS Fecha_Vencimiento'),
                DB::raw('"" AS Observaciones')
            )
            ->leftJoin('Costo_Promedio as CP', 'CP.Id_Producto', '=', 'PRD.Id_Producto')
            ->whereNotNull('PRD.Codigo_Barras')
            ->where('PRD.Estado', 'Activo');
    
        if ($term) {
            $productos->where(function($query) use ($term) {
                $query->where('PRD.Nombre_General', 'LIKE', '%' . $term . '%')
                      ->orWhere('PRD.Nombre_Comercial', 'LIKE', '%' . $term . '%');
            });
        }
    
        $productos = $productos->get();
    
        return $this->success($productos);
    }


    public function productoCodigoBarras()
    {
        $http_response = new HttpResponse();

        $codigo = isset($_REQUEST['codigo']) ? $_REQUEST['codigo'] : '';
        $TipoAjuste = isset($_REQUEST['tipoAjuste']) ? $_REQUEST['tipoAjuste'] : '';
        $tipoSelected = isset($_REQUEST['tipoSelected']) ? $_REQUEST['tipoSelected'] : '';
        $id = isset($_REQUEST['id']) ? strtolower($_REQUEST['id']) : '';

        $query = '';

        if ($TipoAjuste != 'Lotes') {
            # code...
            if ($tipoSelected == 'Bodega') {
                # buscamos de bodega

                $query = $this->BuscarEnBodega($id, $codigo, $TipoAjuste);

            } else if ($tipoSelected == 'Punto') {
                #buscamos en pundo
                $query = $this->BuscarEnPunto($id, $codigo, $TipoAjuste);
            }

        } else if ($TipoAjuste == 'Lotes' && $TipoAjuste != '') {

            if ($tipoSelected == 'Bodega') {
                # buscamos estiba

                $query = $this->BuscarEnlotes($id, $codigo, $tipoSelected);

            } else if ($tipoSelected == 'Punto') {
                #buscamos en pundo
            }
        }



        $oCon = new consulta();
        $oCon->setQuery($query);
        //$oCon->setTipo('Multiple');


        $resultado = array();


        if ($TipoAjuste == 'Entrada') {
            //$oCon->setTipo('simple');          
            $resultado = $oCon->getData();
            unset($oCon);
            $resultado = $resultado == false ? [] : $resultado;
        } else {
            $oCon->setTipo('Multiple');
            $productos = $oCon->getData();
            unset($oCon);


            if ($TipoAjuste != 'Lotes') {
                # code...

                $resultado = $this->AsignarLotes($productos);
            } else {

                // $resultado['Cantidad_Presentacion'] = $productos[0]['Cantidad_Presentacion'];
                // $resultado['Embalaje'] = $productos[0]['Embalaje'];
                $resultado['Id_Producto'] = $productos[0]['Id_Producto'];
                $resultado['Nombre'] = $productos[0]['Nombre'];
                //  $resultado['Cantidad_Presentacion'] = $productos[0]['Cantidad_Presentacion'];
                $resultado['Lotes'] = $productos;

            }
        }

        if (count($resultado) == 1 || count($resultado) == 0) {

            $object = (object) reset($resultado);
        } else {
            $object = $resultado;
        }


        $http_response->SetDatosRespuesta($object);
        if ($resultado != '') {
            $http_response->SetRespuesta(0, 'Consulta Exitosa', 'Se han encontrado datos!');
        } else {
            $http_response->SetRespuesta(2, 'Consulta Exitosa', 'No se han encontrado datos!');
        }

        $resultado = $http_response->GetRespuesta();

        unset($oCon);
        return $this->success($resultado);
    }

    private function BuscarEnBodega($id, $codigo, $TipoAjuste)
    {
        $where = '';

        if ($TipoAjuste == 'Entrada') {
            $where = 'WHERE E.Id_Bodega_Nuevo = ' . $id;
        } else {
            $where = 'WHERE I.Id_Estiba = ' . $id . ' AND (I.Cantidad - (I.Cantidad_Apartada + I.Cantidad_Seleccionada)) > 0';
        }

        $query = 'SELECT 
                    PRD.Id_Producto, 
                    IFNULL(C.Costo_Promedio, 0) AS Precio_Venta, 
                    I.Cantidad, 
                    I.Cantidad_Apartada,
                    I.Cantidad_Seleccionada,
                    IFNULL(C.Costo_Promedio, 0) AS Costo,
                    PRD.Nombre_General AS Nombre,
                    PRD.Nombre_Comercial, 
                    I.Id_Inventario_Nuevo,  
                    IFNULL(C.Costo_Promedio, 0) AS precio,
                    CONCAT(
                        \'{"label":"Lote: \', 
                        COALESCE(I.Lote, \'\'), 
                        \' - Vencimiento: \', 
                        COALESCE(I.Fecha_Vencimiento, \'\'), 
                        \' - Cantidad: \', 
                        COALESCE(I.Cantidad - I.Cantidad_Apartada - I.Cantidad_Seleccionada, 0), 
                        \'", "value":\', 
                        COALESCE(I.Id_Inventario_Nuevo, \'\'), 
                        \', "Codigo_Cum":"\', 
                        \'", "Fecha_Vencimiento":"\', 
                        COALESCE(I.Fecha_Vencimiento, \'\'), 
                        \'", "Lote":"\', 
                        COALESCE(REPLACE(I.Lote, CHAR(13), \'\'), \'\'), 
                        \'", "Cantidad":\', 
                        COALESCE(I.Cantidad - I.Cantidad_Apartada - I.Cantidad_Seleccionada, 0), 
                        \', "Costo":\', 
                        COALESCE(IFNULL(C.Costo_Promedio, 0), 0), 
                        \', "Id_Inventario_Nuevo":\', 
                        COALESCE(I.Id_Inventario_Nuevo, \'\'), 
                        \', "Cantidad_Apartada":\', 
                        COALESCE(I.Cantidad_Apartada, 0), 
                        \', "Nombre":"\', 
                        COALESCE(PRD.Nombre_General, \'\'), 
                        \'", "Embalaje":"\', 
                        \'", "Laboratorio_Comercial":"\', 
                        \'", "Id_Producto":\', 
                        COALESCE(PRD.Id_Producto, \'\'), 
                        \'}\' 
                    ) AS Lote
                FROM 
                    Inventario_Nuevo I
                INNER JOIN 
                    Producto PRD ON I.Id_Producto = PRD.Id_Producto   
                INNER JOIN 
                    Estiba E ON E.Id_Estiba = I.Id_Estiba
                LEFT JOIN 
                    Costo_Promedio C ON C.Id_Producto = PRD.Id_Producto
                ' . $where . '
                    AND PRD.Codigo_Barras LIKE "' . $codigo . '%" 
                ORDER BY 
                    PRD.Id_Producto, I.Fecha_Vencimiento ASC';

        return $query;
    }


    private function BuscarEnPunto($id, $codigo, $TipoAjuste)
    {
        $cond = '';
        if ($TipoAjuste == 'Salida') {
            $cond = ' AND (I.Cantidad - (I.Cantidad_Apartada + I.Cantidad_Seleccionada)) > 0 ';
        }

        $query = 'SELECT 
            PRD.Id_Producto, 
            IFNULL(C.Costo_Promedio, 0) as Precio_Venta, 
            IFNULL(C.Costo_Promedio, 0) as Costo, 
            I.Cantidad,
            I.Cantidad_Apartada, 
            I.Cantidad_Seleccionada,
            PRD.Nombre_General as Nombre,  
            PRD.Nombre_Comercial, 
            I.Id_Inventario_Nuevo, 
            IFNULL(C.Costo_Promedio, 0) as precio,
            CONCAT(
                \'{"label":\',
                CONCAT(
                    \'"Lote:\', TRIM(I.Lote), \' - Vencimiento: \', I.Fecha_Vencimiento, \' - Cantidad: \', 
                    (I.Cantidad - I.Cantidad_Apartada - I.Cantidad_Seleccionada), \'"\'
                ), 
                \',"value":\', I.Id_Inventario_Nuevo, 
                \',"Codigo_Cum":"\',
                \',"Fecha_Vencimiento":"\', I.Fecha_Vencimiento, 
                \',"Lote":"\', TRIM(I.Lote), 
                \',"Cantidad":"\', (I.Cantidad - I.Cantidad_Apartada - I.Cantidad_Seleccionada), 
                \',"Costo":"\', IFNULL(C.Costo_Promedio, 0), 
                \',"Id_Inventario_Nuevo":"\', I.Id_Inventario_Nuevo, 
                \',"Id_Categoria":"\', PRD.Id_Categoria, 
                \',"Cantidad_Apartada":"\', I.Cantidad_Apartada, 
                \',"Nombre":"\', CONCAT_WS(\' \', PRD.Nombre_Comercial, \'CUM:\', PRD.Referencia), 
                \',"Id_Producto":"\', PRD.Id_Producto, \'"}\'
            ) as Lote
        FROM Inventario_Nuevo I
        INNER JOIN Producto PRD
            ON I.Id_Producto = PRD.Id_Producto   
        LEFT JOIN Costo_Promedio C
            ON C.Id_Producto = PRD.Id_Producto
        WHERE I.Id_Punto_Dispensacion=' . $id . ' AND PRD.Codigo_Barras LIKE "' . $codigo . '" ' . $cond . '
        ORDER BY PRD.Id_Producto, I.Fecha_Vencimiento ASC';

        return $query;
    }



    private function BuscarEnLotes($id, $codigo, $tipoSelected)
    {
        switch ($tipoSelected) {
            case "Bodega": {
                $query = 'SELECT   PRD.Id_Producto , I.Lote , SUM(I.Cantidad - (I.Cantidad_Apartada + I.Cantidad_Seleccionada)) AS Cantidad,
            I.Fecha_Vencimiento,IFNULL(C.Costo_Promedio,0) as Precio_Venta, 
            PRD.Embalaje, I.Id_Estiba,

            PRD.Nombre_General as Nombre,


            PRD.Nombre_Comercial, I.Id_Inventario_Nuevo, IFNULL(C.Costo_Promedio,0) as precio
        FROM Inventario_Nuevo I
        INNER JOIN Producto PRD
        On I.Id_Producto=PRD.Id_Producto 
        LEFT JOIN Costo_Promedio C
         ON C.Id_Producto = I.Id_Producto  
        WHERE I.Id_Estiba=' . $id . ' AND (I.Cantidad - (I.Cantidad_Apartada + I.Cantidad_Seleccionada) )>0 
        AND PRD.Codigo_Barras LIKE "' . $codigo . '" 
        GROUP BY I.Id_Inventario_Nuevo
        ORDER BY   I.Fecha_Vencimiento ASC';
                break;
            }
            case "Punto": {
                $query = 'SELECT   PRD.Id_Producto,I.Costo as Precio_Venta, PRD.Embalaje, I.Cantidad, I.Cantidad_Apartada,
                CONCAT_WS(" ",PRD.Nombre_Comercial,PRD.Nombre_General as Nombre,  
                    PRD.Nombre_Comercial,I.Id_Inventario_Nuevo, I.Costo as precio,PRD.Cantidad_Presentacion
                
                    FROM Inventario_Nuevo I
                    INNER JOIN Producto PRD
                    On I.Id_Producto=PRD.Id_Producto   
                WHERE I.Id_Punto_Dispensacion=' . $id . ' AND (I.Cantidad - (I.Cantidad_Apartada + I.Cantidad_Seleccionada) )>0 
                AND PRD.Codigo_Barras LIKE "' . $codigo . '" 
                GROUP BY I.Id_Producto
                ORDER BY  PRD.Id_Producto, I.Fecha_Vencimiento ASC';
                break;
            }
        }
        return $query;
    }

    function AsignarLotes($productos)
    {
        $resultado = [];
        $idproducto = '';
        $pos = -1;
        $poslotes = 0;
        $lotes = [];
        $cantidad_disponible = 0;

        foreach ($productos as $producto) {
            if ($producto->Id_Producto != $idproducto) {
                $pos++;
                if ($pos > 0) {
                    $resultado[$pos - 1]["Lotes"] = $lotes;
                    $resultado[$pos - 1]["Cantidad_Disponible"] = $cantidad_disponible;
                }
                $idproducto = $producto->Id_Producto;
                $lotes = [];
                $cantidad_disponible = 0;
                if (empty($producto->Nombre)) {
                    $resultado[$pos]["Nombre"] = $producto->Nombre_Comercial;
                } else {
                    $resultado[$pos]["Nombre"] = $producto->Nombre;
                }
                $resultado[$pos]["precio"] = $producto->precio;
                $resultado[$pos]["Precio_Venta"] = $producto->precio;
                $resultado[$pos]["Id_Inventario_Nuevo"] = $producto->Id_Inventario_Nuevo;
            }

            $lotes[$poslotes] = json_decode(trim(preg_replace('`\s+`', '', $producto->Lote), true));
            $cantidad_disponible += ($producto->Cantidad - $producto->Cantidad_Apartada - $producto->Cantidad_Seleccionada);
            $poslotes++;
        }

        $resultado[$pos]["Lotes"] = $lotes;
        $resultado[$pos]["Cantidad_Disponible"] = $cantidad_disponible;

        return $resultado;
    }

    public function detalleAjuste1(Request $request)
    {
        $id = $request->input('id');

        // Consulta principal
        $ajuste = AjusteIndividual::selectRaw('Ajuste_Individual.*, Estiba.Nombre AS Nombre_Estiba_Salida,
         people.full_name as Funcionario, positions.name as Cargo_Funcionario, people.signature as Firma,
         IF(Ajuste_Individual.Origen_Destino = "Bodega",(SELECT B.Nombre FROM Bodega_Nuevo B WHERE B.Id_Bodega_Nuevo=Ajuste_Individual.Id_Origen_Destino),
        (SELECT B.Nombre FROM Punto_Dispensacion B WHERE B.Id_Punto_Dispensacion=Ajuste_Individual.Id_Origen_Destino)) as Origen')
            ->leftJoin('Estiba', 'Estiba.Id_Estiba', '=', 'Ajuste_Individual.Id_Origen_Estiba')
            ->leftJoin('people', 'people.identifier', '=', 'Ajuste_Individual.Identificacion_Funcionario')
            ->leftJoin('work_contracts', 'people.id', '=', 'work_contracts.person_id')
            ->leftJoin('positions', 'positions.id', '=', 'work_contracts.position_id')
            ->where('Ajuste_Individual.Id_Ajuste_Individual', $id)
            ->where('work_contracts.liquidated', '0')
            ->first();

        if (!$ajuste) {
            return response()->json(['error' => 'Ajuste individual no encontrado'], 404);
        }

        // Acceder a las relaciones si están definidas en los modelos

        // Lógica adicional para buscar la entrada si existe
        if ($ajuste->Cambio_Estiba) {
            $condicion = $ajuste->Id_Salida ? 'Ajuste_Individual.Id_Ajuste_Individual = ' . $ajuste->Id_Salida : 'Ajuste_Individual.Id_Salida = ' . $ajuste->Id_Ajuste_Individual;

            $entrada = AjusteIndividual::whereRaw($condicion)->first();

            if ($entrada) {
                $ajuste->entrada = $entrada;
            }
        }

        // Consulta de productos por ajuste individual
        $productos = ProductoAjusteIndividual::selectRaw('Producto.Nombre_Comercial, Estiba.Nombre AS Nombre_Nueva_Estiba, (Producto_Ajuste_Individual.Cantidad * Producto_Ajuste_Individual.Costo) AS Sub_Total,
                Producto.Nombre_General AS Nombre_Producto,
                Producto_Ajuste_Individual.Lote, Producto_Ajuste_Individual.Fecha_Vencimiento, Producto_Ajuste_Individual.Cantidad, Producto_Ajuste_Individual.Observaciones, Producto_Ajuste_Individual.Costo')
            ->join('Producto', 'Producto_Ajuste_Individual.Id_Producto', '=', 'Producto.Id_Producto')
            ->leftJoin('Estiba', 'Estiba.Id_Estiba', '=', 'Producto_Ajuste_Individual.Id_Estiba_Acomodada')
            ->where('Producto_Ajuste_Individual.Id_Ajuste_Individual', $id)
            ->get();

        $total = $productos->sum(function ($producto) {
            return $producto->Cantidad * $producto->Costo;
        });

        $ajuste->productos = $productos;
        $ajuste->Total = $total;

        return $this->success([$ajuste]);
    }

    public function listaProductoInventario(Request $request)
    {
        $tipo = (isset($_REQUEST['tipo']) ? $_REQUEST['tipo'] : '');
        $id = (isset($_REQUEST['id']) ? $_REQUEST['id'] : '');
    
        switch ($tipo) {
            case "Bodega": {
                $query = "SELECT
                    PRD.Id_Producto,
                    IFNULL(C.Costo_Promedio,0) as Precio_Venta,
                    I.Cantidad, I.Cantidad_Apartada,
                    PRD.Nombre_General as Nombre, I.Cantidad_Seleccionada,
                    PRD.Nombre_Comercial, I.Id_Inventario_Nuevo, IFNULL(C.Costo_Promedio,0) as precio,
                    CONCAT_WS('','{\"label\":',
                            CONCAT_WS('','\"Lote: ',I.Lote,
                                ' - Vencimiento: ',  I.Fecha_Vencimiento,
                                ' - Cantidad: ',(I.Cantidad-I.Cantidad_Apartada-I.Cantidad_Seleccionada),
                            '\"'),
                        ',\"value\":\"',I.Id_Inventario_Nuevo,
                        '\",\"Fecha_Vencimiento\":\"',I.Fecha_Vencimiento,
                        '\",\"Lote\":\"',REPLACE(I.Lote,CHAR(13,10),''),
                        '\",\"Cantidad\":\"',(I.Cantidad-I.Cantidad_Apartada-I.Cantidad_Seleccionada),
                        '\",\"Costo\":\"',IFNULL(C.Costo_Promedio,0),
                        '\",\"Id_Inventario_Nuevo\":\"',I.Id_Inventario_Nuevo,
                        '\",\"Cantidad_Apartada\":\"',I.Cantidad_Apartada,
                        '\",\"Nombre\":\"', PRD.Nombre_General,
                        '\",\"Id_Producto\":\"',PRD.Id_Producto,
                        '\"}') as Lote
                    FROM Inventario_Nuevo I
                    INNER JOIN Producto PRD On I.Id_Producto=PRD.Id_Producto
                    LEFT JOIN Costo_Promedio C ON C.Id_Producto = PRD.Id_Producto
                    WHERE I.Id_Estiba=$id AND I.Cantidad>0
                    ORDER BY  PRD.Id_Producto, I.Fecha_Vencimiento ASC";
                break;
            }
            case "Punto": {
                $query = "SELECT
                    PRD.Id_Producto, IFNULL(C.Costo_Promedio,0) as Precio_Venta, I.Cantidad, I.Cantidad_Apartada, I.Cantidad_Seleccionada,
                    PRD.Nombre_General as Nombre,
                    PRD.Nombre_Comercial, I.Id_Inventario_Nuevo, IFNULL(C.Costo_Promedio,0) as precio,
                    CONCAT_WS('',
                        '{\"label\":',
                            CONCAT_WS('','\"Lote: ', TRIM(I.Lote),
                                ' - Vencimiento: ',  I.Fecha_Vencimiento,
                                ' - Cantidad: ',(I.Cantidad-I.Cantidad_Apartada-I.Cantidad_Seleccionada),
                                '\"'),
                        ',\"value\":\"', I.Id_Inventario_Nuevo,
                        '\",\"Fecha_Vencimiento\":\"',I.Fecha_Vencimiento,
                        '\",\"Lote\":\"',TRIM(I.Lote),
                        '\",\"Cantidad\":\"',(I.Cantidad-I.Cantidad_Apartada-I.Cantidad_Seleccionada),
                        '\",\"Costo\":\"',IFNULL(C.Costo_Promedio,0),
                        '\",\"Id_Inventario_Nuevo\":\"',I.Id_Inventario_Nuevo,
                        '\",\"Id_Categoria\":\"',PRD.Id_Categoria,
                        '\",\"Cantidad_Apartada\":\"',I.Cantidad_Apartada,
                        '\",\"Nombre\":\"', PRD.Nombre_General,
                        '\",\"Id_Producto\":\"',PRD.Id_Producto,
                        '\"}') as Lote
                    FROM Inventario_Nuevo I
                    INNER JOIN Producto PRD
                    On I.Id_Producto=PRD.Id_Producto
                    LEFT JOIN Costo_Promedio C
                    ON C.Id_Producto = PRD.Id_Producto
                    INNER JOIN Estiba E on E.Id_Estiba = I.Id_Estiba
                    WHERE E.Id_Punto_Dispensacion=$id AND (I.Cantidad - (I.Cantidad_Apartada + I.Cantidad_Seleccionada) )>0
                        ORDER BY  PRD.Id_Producto, I.Fecha_Vencimiento ASC";
                break;
            }
        }
    
        $oCon = new consulta();
        $oCon->setTipo('Multiple');
        $oCon->setQuery($query);
        $productos = $oCon->getData();
        unset($oCon);
    
        $i = -1;
        $idproducto = '';
        $resultado = [];
        $pos = -1;
        $poslotes = 0;
        $lotes = [];
        $cantidad_disponible = 0;
        foreach ($productos as $producto) {
            $i++;
            if ($producto->Id_Producto != $idproducto) {
                if ($pos >= 0) {
                    $resultado[$pos]["Lotes"] = $lotes;
                    $resultado[$pos]["Cantidad_Disponible"] = $cantidad_disponible;
                    $poslotes = 0;
                }
                $pos++;
                $resultado[$pos]["Id_Producto"] = $producto->Id_Producto;
                $resultado[$pos]["Nombre"] = $producto->Nombre ?: $producto->Nombre_Comercial;
                $resultado[$pos]["precio"] = $producto->precio;
                $resultado[$pos]["Precio_Venta"] = $producto->precio;
                $resultado[$pos]["Id_Inventario_Nuevo"] = $producto->Id_Inventario_Nuevo;
                $idproducto = $producto->Id_Producto;
                $lotes = [];
                $cantidad_disponible = 0;
    
                $cleanedJson = str_replace(array("\\", "\r", "\n"), '', $producto->Lote);
                $decodedLote = json_decode($cleanedJson, true);
    
                if (json_last_error() === JSON_ERROR_NONE) {
                    $lotes[$poslotes] = $decodedLote;
                } else {
                    error_log("Error decodificando JSON: " . json_last_error_msg() . " - JSON: " . $cleanedJson);
                    $lotes[$poslotes] = null;
                }
    
                $cantidad_disponible += ($producto->Cantidad - $producto->Cantidad_Apartada - $producto->Cantidad_Seleccionada);
            } else {
                $poslotes++;
                $cleanedJson = str_replace(array("\\", "\r", "\n"), '', $producto->Lote);
                $decodedLote = json_decode($cleanedJson, true);
    
                if (json_last_error() === JSON_ERROR_NONE) {
                    $lotes[$poslotes] = $decodedLote;
                } else {
                    error_log("Error decodificando JSON: " . json_last_error_msg() . " - JSON: " . $cleanedJson);
                    $lotes[$poslotes] = null;
                }
    
                $cantidad_disponible += $producto->Cantidad - $producto->Cantidad_Apartada - $producto->Cantidad_Seleccionada;
            }
    
        }
    
        $resultado[$pos]["Lotes"] = $lotes;
    
        return $this->success($resultado);
    }
    

    public function reporteAjusteIndividual()
    {
        $queryObj = new QueryBaseDatos();
        $http_response = new HttpResponse();
        $response = array();
        $condiciones = $this->SetConditions($_REQUEST);
        $ajustes = $this->GetAjsutes($condiciones, $queryObj);
        return $this->ArmarTablaResultados($ajustes);
    }

    private function SetConditions($req)
    {
        $condicion = '';

        if (isset($req['fechas']) && $req['fechas']) {
            $fechas = $this->SepararFechas($req['fechas']);

            if ($condicion != "") {
                $condicion .= " AND DATE(A.Fecha) BETWEEN '" . $fechas[0] . "' AND '" . $fechas[1] . "'";
            } else {
                $condicion .= " WHERE DATE(A.Fecha) BETWEEN '" . $fechas[0] . "' AND '" . $fechas[1] . "'";
            }
        }

        if (isset($req['tipo']) && $req['tipo']) {
            $tipo = $_REQUEST['tipo'];
            if ($tipo != '') {
                if ($condicion != "") {
                    $condicion .= " AND A.Tipo  ='$tipo'";
                } else {
                    $condicion .= " WHERE A.Tipo ='" . $tipo . "' ";
                }
            }

        }
        if (isset($req['origen']) && $req['origen']) {
            $origen = $_REQUEST['origen'];
            if ($origen != '') {
                if ($condicion != "") {
                    $condicion .= " AND A.Origen_Destino ='$origen' ";
                } else {
                    $condicion .= " WHERE A.Origen_Destino ='" . $origen . "' ";
                }
            }

        }
        if (isset($req['id']) && $req['id']) {
            $id = $_REQUEST['id'];
            if ($id != '') {
                if ($condicion != "") {
                    $condicion .= " AND A.Id_Origen_Destino=  '$id' ";
                } else {
                    $condicion .= " WHERE A.Id_Origen_Destino='" . $id . "' ";
                }
            }

        }

        return $condicion;
    }

    private function SepararFechas($fechas)
    {
        $fechas_separadas = explode(" - ", $fechas);
        return $fechas_separadas;
    }

    private function GetAjsutes($condiciones, $queryObj)
    {

        $query_ajuste = 'SELECT P.Nombre_Comercial, P.Nombre_General as Producto,
         PAI.Fecha_Vencimiento,
         PAI.Lote,
         PAI.Costo,
         PAI.Cantidad,(PAI.Cantidad*PAI.Costo) as Subtotal,
         PAI.Observaciones, 
         (
          CASE  
           WHEN A.Origen_Destino = "Bodega"  THEN CONCAT_WS(" ", "BODEGA", (SELECT Nombre FROM Bodega_Nuevo WHERE Id_Bodega_Nuevo=A.Id_Origen_Destino))
           ELSE  (SELECT Nombre FROM Punto_Dispensacion WHERE Id_Punto_Dispensacion=A.Id_Origen_Destino)
         END
         ) as Destino,                  
        DATE(A.Fecha) As Fecha,A.Tipo,
        A.Codigo, A.Estado, PAI.Observaciones,
            A.Observacion_Anulacion,
        (SELECT full_name FROM people WHERE people.identifier=A.Identificacion_Funcionario) as Funcionario, (SELECT full_name FROM people WHERE people.identifier=A.Funcionario_Anula) as Funcionario_Anula, DATE(A.Fecha_Anulacion) as Fecha_Anulacion
        FROM Producto_Ajuste_Individual PAI 
        INNER JOIN Producto P ON PAI.Id_Producto=P.Id_Producto
        INNER JOIN Ajuste_Individual A ON PAI.Id_Ajuste_Individual=A.Id_Ajuste_Individual 
        ' . $condiciones;



        $queryObj->SetQuery($query_ajuste);
        $ajuste = $queryObj->ExecuteQuery('multiple');
        return $ajuste;
    }

    function ArmarTablaResultados($resultados)
    {

        $contenido_excel = '';

        $contenido_excel = '
        <table border=1>
        <tr>
            <td align="center"><strong>Codigo Ajuste</strong></td>
            <td align="center"><strong>Fecha</strong></td>
            <td align="center"><strong>Funcionario </strong></td>
            <td align="center"><strong>Nombre Comercial</strong></td>
            <td align="center"><strong>Producto</strong></td>
            <td align="center"><strong>Tipo</strong></td>
            <td align="center"><strong>Observacion</strong></td>
            <td align="center"><strong>Origen</strong></td>
            <td align="center"><strong>Lote</strong></td>
            <td align="center"><strong>Fecha Vencimiento</strong></td>
            <td align="center"><strong>Cantidad</strong></td>
            <td align="center"><strong>Costo</strong></td>
        </tr>';

        if (count($resultados) > 0) {
            foreach ($resultados as $i => $r) {

                $contenido_excel .= '
                <tr>
                    <td>' . $r->Codigo . '</td>
                    <td>' . $r->Fecha . '</td>
                    <td>' . $r->Funcionario . '</td>
                    <td>' . $r->Nombre_Comercial . '</td>
                    <td>' . $r->Producto . '</td>
                    <td>' . $r->Tipo . '</td>
                    <td>' . $r->Observaciones . '</td>
                    <td>' . $r->Destino . '</td>
                    <td>' . $r->Lote . '</td>
                    <td>' . $r->Fecha_Vencimiento . '</td>
                    <td>' . $r->Cantidad . '</td>
                    <td>' . $r->Costo . '</td>
                </tr>';
            }
        } else {

            $contenido_excel .= '
            <tr>
                <td colspan="8" align="center">SIN RESULTADOS PARA MOSTRAR</td>
            </tr>';
        }


        $contenido_excel .= '
        </table>';

        echo $contenido_excel;
    }

    public function guardarSalida()
    {

        $funcionario = (isset($_REQUEST['funcionario']) ? $_REQUEST['funcionario'] : '');
        $productos = (isset($_REQUEST['productos']) ? $_REQUEST['productos'] : '');
        $datos = (isset($_REQUEST['datos']) ? $_REQUEST['datos'] : '');
        $tipo_ajuste = (isset($_REQUEST['tipo_ajuste']) ? $_REQUEST['tipo_ajuste'] : '');

        $contabilizacion = new Contabilizar(true);

        if ($tipo_ajuste == "Salida" || $tipo_ajuste == "Cambio") {
            # code...

            $productos = (array) json_decode($productos, true);
            $datos = (array) json_decode($datos);

            if (!isset($datos['Tipo'])) {
                $datos['Tipo'] = 'Bodega';
            }

            $cod = generateConsecutive('Ajuste_Individual');
            sumConsecutive('Ajuste_Individual');
            // var_dump($cod);exit;
            $oItem = new complex('Ajuste_Individual', 'Id_Ajuste_Individual');
            $oItem->Identificacion_Funcionario = $funcionario;
            $oItem->Codigo = $cod;
            $oItem->Tipo = "Salida";
            $oItem->company_id = getCompanyWorkedId();
            $oItem->Id_Clase_Ajuste_Individual = isset($datos['Id_Clase_Ajuste_Individual']) ? $datos['Id_Clase_Ajuste_Individual'] : 1;
            $oItem->Origen_Destino = $datos['Tipo'] ?? 'Bodega';


            if ($datos['Tipo'] == "Bodega" || $tipo_ajuste == "Cambio") {
                # code...
                $oItem->Id_Origen_Estiba = $datos['Id_Estiba'] ?? null;
                $oItem->Id_Origen_Destino = $datos['Id_Bodega_Nuevo'];
                $oItem->Estado_Salida_Bodega = 'Pendiente';
                $oItem->Cambio_Estiba = $tipo_ajuste == 'Cambio' ? '1' : '0';


            } else if ($datos['Tipo'] == "Punto") {

                $oItem->Id_Origen_Destino = $datos['Id_Punto_Dispensacion'];
                $oItem->Estado_Salida_Bodega = 'Aprobado';
            }
            $oItem->save();
            $id_ajuste = $oItem->getId();
            unset($oItem);

            if ($id_ajuste) {
                $this->guardarActividad($id_ajuste, $funcionario, 'Se creó la salida del ajuste individual ' . $cod, 'Creacion');
            }
            /* AQUI GENERA QR 

            $qr = generarqr('ajusteindividual',$id_ajuste,'IMAGENES/QR/');
            $oItem = new complex("Ajuste_Individual","Id_Ajuste_Individual",$id_ajuste);
            $oItem->Codigo_Qr=$qr;
            $oItem->save();
            unset($oItem);
            HASTA AQUI GENERA QR */


            foreach ($productos as $producto) {
                //Descontar del inventario

                $oItem = new complex('Inventario_Nuevo', 'Id_Inventario_Nuevo', $producto["Id_Inventario_Nuevo"]);

                if ($datos['Tipo'] == "Punto") {

                    $cantidad = number_format($producto["Cantidad_Actual"], 0, "", "");
                    $cantidad_final = $oItem->Cantidad - $cantidad;
                    if ($cantidad_final < 0) {
                        $cantidad_final = 0;
                    }
                    $oItem->Cantidad = number_format($cantidad_final, 0, "", "");

                    $oItem->save();
                    unset($oItem);

                }

                $id_inventario_nuevo = $producto["Id_Inventario_Nuevo"];


                $oItem = new complex('Producto_Ajuste_Individual', 'Id_Producto_Ajuste_Individual');
                $oItem->Id_Ajuste_Individual = $id_ajuste;
                $oItem->Id_Producto = $producto["Id_Producto"];
                $oItem->Id_Inventario_Nuevo = $id_inventario_nuevo;
                $oItem->Lote = trim(strtoupper($producto['Lote']));
                $oItem->Fecha_Vencimiento = $producto['Fecha_Vencimiento'];
                $oItem->Observaciones = $producto['Observaciones'];
                $oItem->Cantidad = $producto['Cantidad_Actual'];
                $oItem->Costo = $producto['Costo'];

                if ($tipo_ajuste == 'Cambio') {
                    $oItem->Id_Nueva_Estiba = $producto['Nueva_Estiba'];

                }

                $oItem->save();
                unset($oItem);

            }


            if ($id_inventario_nuevo && $datos['Tipo'] == "Punto") {

                $datos_movimiento_contable['Id_Registro'] = $id_ajuste;
                $datos_movimiento_contable['Nit'] = $funcionario;
                $datos_movimiento_contable['Tipo'] = "Salida";
                $datos_movimiento_contable['Clase_Ajuste'] = $datos['Id_Clase_Ajuste_Individual'];
                $datos_movimiento_contable['Productos'] = $productos;

                $contabilizacion = new Contabilizar(true);
                $contabilizacion->CrearMovimientoContable('Ajuste Individual', $datos_movimiento_contable);
                unset($contabilizacion);

                $resultado['mensaje'] = "Se ha guarda correctamente la salida de los productos";
                $resultado['tipo'] = "success";
                $resultado['titulo'] = "Operación Exitosa";

            } else if ($id_inventario_nuevo) {

                $resultado['mensaje'] = "Se generó el proceso de salida de los productos - PENDIENTE APROBACIÓN";
                $resultado['tipo'] = "success";
                $resultado['titulo'] = "Operación Exitosa";

            } else {

                $resultado['mensaje'] = "Ha ocurrido un error inesperado. Por favor intentelo de nuevo";
                $resultado['tipo'] = "error";
                $resultado['titulo'] = "Error";

            }
        } else {
            $resultado['mensaje'] = "El tipo de ajuste no es permitido";
            $resultado['tipo'] = "error";
            $resultado['titulo'] = "Ha ocurrido un error inesperado.";
        }

        return $this->success($resultado);
    }

    private function guardarActividad($id_ajuste, $funcionario, $detalle, $estado)
    {
        $oItem = new complex('Actividad_Ajuste_Individual', 'Id_Actividad_Ajuste_Individual');
        $oItem->Id_Ajuste_Individual = $id_ajuste;
        $oItem->Identificacion_Funcionario = auth()->user()->person_id;
        $oItem->Detalle = $detalle;
        $oItem->Estado = $estado;

        $oItem->save();
    }

    public function guardarEntrada()
    {
        $productos = (isset($_REQUEST['productos']) ? $_REQUEST['productos'] : '');
        $datos = (isset($_REQUEST['datos']) ? $_REQUEST['datos'] : '');
        $funcionario = (isset($_REQUEST['funcionario']) ? $_REQUEST['funcionario'] : '');
        $tipo_ajuste = (isset($_REQUEST['tipo_ajuste']) ? $_REQUEST['tipo_ajuste'] : '');
        $cod = '';
        $productos = json_decode($productos, true);
        $datos = json_decode($datos, true);


        if ($tipo_ajuste == 'Entrada') {

            if ($datos['Tipo'] == 'Bodega') {
                //creamos el ajuste individual

                $id_ajuste = $this->Save_Encabezado('Bodega', $datos, $funcionario, $cod);
                /* AQUI GENERA QR 
               $qr = generarqr('ajusteindividual',$id_ajuste,'IMAGENES/QR/');
               $oItem = new complex("Ajuste_Individual","Id_Ajuste_Individual",$id_ajuste);
               $oItem->Codigo_Qr=$qr;
               $oItem->save();
               unset($oItem);
               HASTA AQUI GENERA QR */


                foreach ($productos as $key => $producto) {
                    if (isset($producto['Costo_Nuevo'])) {
                        # code...
                        $productos[$key]['Costo'] = $producto['Costo_Nuevo'];
                        /*   Guardar_Costo_Nuevo($producto); */
                    }

                    $this->Guardar_Producto_Ajuste($productos[$key], $id_ajuste);
                }
                if ($id_ajuste) {
                    $this->guardarActividad($id_ajuste, $funcionario, 'Se creó la entrada del ajuste individual ' . $cod, 'Creacion');
                    $datos_movimiento_contable['Id_Registro'] = $id_ajuste;
                    $datos_movimiento_contable['Nit'] = $funcionario;
                    $datos_movimiento_contable['Tipo'] = "Entrada";
                    $datos_movimiento_contable['Clase_Ajuste'] = $datos['Id_Clase_Ajuste_Individual'];
                    $datos_movimiento_contable['Productos'] = $productos;

                    $contabilizacion = new Contabilizar(true);
                    $contabilizacion->CrearMovimientoContable('Ajuste Individual', $datos_movimiento_contable);
                    unset($contabilizacion);

                    # code...
                    $response['tipo'] = 'success';
                    $response['title'] = 'Ajuste Indivual creado exitosamente ';
                    $response['mensaje'] = '¡Se ha creado el ajuste de entrada, ahora puede acomodar los productos!';

                } else {
                    $response['tipo'] = 'error';
                    $response['title'] = 'Error inesperado ';
                    $response['mensaje'] = '¡Ha ocurrido un error, comuníquese con el Dpt. de sistemas!';
                }


            } else if ($datos['Tipo'] == 'Punto') {

                $id_ajuste = $this->Save_Encabezado('Punto', $datos, $funcionario, $cod);

                foreach ($productos as $key => $producto) {
                    if ($producto['Costo_Nuevo']) {
                        # code...
                        $productos[$key]['Costo'] = $producto['Costo_Nuevo'];
                        /*   Guardar_Costo_Nuevo($producto); */
                    }

                    if ($datos['Id_Punto_Dispensacion'] != "") {
                        $query = "SELECT Id_Inventario_Nuevo, Cantidad 
                        FROM Inventario_Nuevo
                            WHERE Id_Producto=" . $producto['Id_Producto'] . "
                            AND Lote='" . trim($producto['Lote']) . "'
                            #AND Fecha_Vencimiento='" . $producto['Fecha_Vencimiento'] . "' 
                            AND Id_Punto_Dispensacion=" . $datos['Id_Punto_Dispensacion']

                        ;

                    }

                    $oCon = new consulta();
                    $oCon->setQuery($query);


                    $inventario = $oCon->getData();
                    unset($oCon);

                    if ($inventario) { // Si existe el producto en el inventario
                        $oItem = new complex('Inventario_Nuevo', 'Id_Inventario_Nuevo', $inventario['Id_Inventario_Nuevo']);
                        $cantidad = number_format($producto["Cantidad"], 0, "", "");
                        $cantidad_inventario = number_format($inventario["Cantidad"], 0, "", "");
                        $cantidad_final = $cantidad_inventario + $cantidad;
                        $oItem->Cantidad = $cantidad_final;
                        /*   $costo = number_format($producto["Costo"],0,".",""); */
                        // $oItem->Costo = $costo;
                        $id_inventario_nuevo = $oItem->Id_Inventario_Nuevo;
                    } else {
                        $oItem = new complex('Inventario_Nuevo', 'Id_Inventario_Nuevo');
                        $cantidad = number_format($producto["Cantidad"], 0, "", "");
                        $oItem->Cantidad = $cantidad;
                        $oItem->Id_Producto = $producto["Id_Producto"];
                        $oItem->Codigo_CUM = $producto["Codigo_Cum"];
                        $oItem->Lote = trim(strtoupper($producto['Lote']));
                        $oItem->Fecha_Vencimiento = $producto["Fecha_Vencimiento"];
                        $oItem->Id_Bodega = 0;
                        $oItem->Id_Punto_Dispensacion = $datos["Id_Punto_Dispensacion"];
                        $costo = number_format($productos[$key]["Costo"], 0, ".", "");
                        $oItem->Costo = $costo;
                        $oItem->Cantidad_Apartada = 0;
                    }

                    $oItem->Identificacion_Funcionario = $funcionario;

                    $oItem->save();


                    if (!$inventario) { // Si no existe el producto en el inventario obtengo el último id registrado
                        $id_inventario_nuevo = $oItem->getId();
                    }
                    unset($oItem);
                    $this->Guardar_Producto_Ajuste($productos[$key], $id_ajuste, $id_inventario_nuevo);

                }

                if ($id_ajuste) {
                    # code...



                    $this->guardarActividad($id_ajuste, $funcionario, 'Se creó la entrada del ajuste individual ' . $cod, 'Creacion');

                    $datos_movimiento_contable['Id_Registro'] = $id_ajuste;
                    $datos_movimiento_contable['Nit'] = $funcionario;
                    $datos_movimiento_contable['Tipo'] = "Entrada";
                    $datos_movimiento_contable['Clase_Ajuste'] = $datos['Id_Clase_Ajuste_Individual'];
                    $datos_movimiento_contable['Productos'] = $productos;


                    $contabilizacion = new Contabilizar(true);
                    $contabilizacion->CrearMovimientoContable('Ajuste Individual', $datos_movimiento_contable);
                    unset($contabilizacion);


                    $response['mensaje'] = "Se ha guarda correctamente la Entrada en el Punto";
                    $response['tipo'] = "success";
                    $response['title'] = "Operación Exitosa";
                }

            }

        } else {
            $response['tipo'] = 'error';
            $response['title'] = 'Error inesperado ';
            $response['mensaje'] = '¡Ha ocurrido un error, comuníquese con el Dpt. de sistemas!';
        }

        return $this->success($response);
    }

    private function Save_Encabezado($tipo, $datos, $funcionario, $cod)
    {
        $cod = generateConsecutive('Ajuste_Individual');
        sumConsecutive('Ajuste_Individual');


        $oItem = new complex('Ajuste_Individual', 'Id_Ajuste_Individual');
        $oItem->Identificacion_Funcionario = $funcionario;
        $oItem->Codigo = $cod;
        $oItem->company_id = getCompanyWorkedId();
        $oItem->Tipo = "Entrada";
        $oItem->Id_Clase_Ajuste_Individual = $datos['Id_Clase_Ajuste_Individual'];


        if ($tipo == 'Punto') {
            $oItem->Origen_Destino = $datos['Tipo'];
            $oItem->Id_Origen_Destino = $datos['Id_Punto_Dispensacion'];
            $oItem->Estado_Entrada_Bodega = 'Aprobada';

        } else if ($tipo == 'Bodega') {
            $oItem->Origen_Destino = $datos['Tipo'];
            $oItem->Id_Origen_Destino = $datos['Id_Bodega_Nuevo'];
            $oItem->Estado_Entrada_Bodega = 'Aprobada';

        }

        $oItem->save();
        $id_ajuste = $oItem->getId();
        unset($oItem);
        return $id_ajuste;
    }

    private function Guardar_Producto_Ajuste($producto, $id_ajuste, $id_inventario_nuevo = false)
    {

        $oItem = new complex('Producto_Ajuste_Individual', 'Id_Producto_Ajuste_Individual');
        $oItem->Id_Ajuste_Individual = $id_ajuste;
        $oItem->Id_Producto = $producto["Id_Producto"];
        $oItem->Lote = trim(strtoupper($producto['Lote']));
        $oItem->Fecha_Vencimiento = $producto['Fecha_Vencimiento'];
        $oItem->Observaciones = $producto['Observaciones'];
        $cantidad1 = number_format($producto["Cantidad"], 0, "", "");
        $oItem->Cantidad = $cantidad1;
        $costo = number_format($producto["Costo"], 5, ".", "");
        $oItem->Costo = $costo;

        if ($id_inventario_nuevo) {
            $oItem->Id_Inventario_Nuevo = $id_inventario_nuevo;
        }

        $oItem->save();
        unset($oItem);

    }

    public function descargaPDF()
    {

        $id = (isset($_REQUEST['id']) ? $_REQUEST['id'] : '');

        $query = "SELECT AI.*, (SELECT F.full_name FROM people F WHERE F.identifier=AI.Identificacion_Funcionario) AS Funcionario, (SELECT p.name FROM people F INNER JOIN work_contracts C ON F.id=C.person_id INNER JOIN positions p ON C.position_id=p.id WHERE F.identifier=AI.Identificacion_Funcionario AND C.liquidated = 0) AS Cargo_Funcionario,
        (SELECT F.signature FROM people F WHERE F.identifier=AI.Identificacion_Funcionario) as Firma
        FROM `Ajuste_Individual` AI WHERE AI.Id_Ajuste_Individual=$id";

        $oCon = new consulta();
        $oCon->setQuery($query);
        $encabezado = $oCon->getData();
        unset($oCon);

        $query = "SELECT P.Nombre_Comercial, P.Nombre_General as Nombre_Producto, PAI.Lote, PAI.Fecha_Vencimiento, PAI.Cantidad, PAI.Observaciones FROM Producto_Ajuste_Individual PAI INNER JOIN Producto P ON PAI.Id_Producto=P.Id_Producto WHERE PAI.Id_Ajuste_Individual=$id";

        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->setTipo('Multiple');
        $productos = $oCon->getData();
        unset($oCon);

        $elabora = Person::where('id', $encabezado["Identificacion_Funcionario"])
            ->value(DB::raw("IFNULL(CONCAT_WS(' ', first_name, first_surname), 'Nombre no disponible')"));

        $header = (object) [
            'Titulo' => 'Ajuste Individual',
            'Codigo' => $encabezado['Codigo'] ?? '',
            'Fecha' => $encabezado['Fecha'],
            'CodigoFormato' => $encabezado['Codigo'] ?? '',
        ];

        $pdf = Pdf::loadView('pdf.ajuste_individual', [
            'productos' => $productos,
            'datosCabecera' => $header,
            'elabora' => $elabora,
            'encabezado' => $encabezado
        ]);

        return $pdf->stream("ajusteIndividual");
    }

    public function listaProductosLotes()
    {
        $tipo = (isset($_REQUEST['tipo']) ? $_REQUEST['tipo'] : '');
        $id = (isset($_REQUEST['id']) ? $_REQUEST['id'] : '');

        switch ($tipo) {

            case "Bodega": {
                $query = 'SELECT   PRD.Id_Producto,IFNULL(C.Costo_Promedio,0) as Precio_Venta, I.Cantidad, I.Cantidad_Apartada, I.Id_Estiba,
	  PRD.Nombre_General as Nombre,
	     PRD.Nombre_Comercial, I.Id_Inventario_Nuevo, IFNULL(C.Costo_Promedio,0) as precio,
	FROM Inventario_Nuevo I
    INNER JOIN Producto PRD
    On I.Id_Producto=PRD.Id_Producto   
	LEFT JOIN Costo_Promedio C
	 ON C.Id_Producto = I.Id_Producto
    WHERE I.Id_Estiba=' . $id . ' AND (I.Cantidad - (I.Cantidad_Apartada + I.Cantidad_Seleccionada) )>0 
	GROUP BY I.Id_Producto, I.Estiba, I.Fecha_Vencimiento
    ORDER BY  PRD.Id_Producto, I.Fecha_Vencimiento ASC';

                break;
            }
            case "Punto": {
                $query = 'SELECT   PRD.Id_Producto,IFNULL(C.Costo_Promedio,0) as Precio_Venta, I.Cantidad, I.Cantidad_Apartada,
		PRD.Nombre_General as Nombre,  
		 PRD.Nombre_Comercial,I.Id_Inventario_Nuevo, IFNULL(C.Costo_Promedio,0) as precio,
		 I.Fecha_Vencimiento, "" as Fecha_Vencimiento_Nueva
		
		FROM Inventario_Nuevo I
		INNER JOIN Producto PRD
		On I.Id_Producto=PRD.Id_Producto   
		LEFT JOIN Costo_Promedio C
	 	ON C.Id_Producto = I.Id_Producto
     WHERE I.Id_Punto_Dispensacion=' . $id . ' AND (I.Cantidad - (I.Cantidad_Apartada + I.Cantidad_Seleccionada) )>0 
        GROUP BY I.Id_Producto, I.Fecha_Vencimiento
      ORDER BY  PRD.Id_Producto, I.Fecha_Vencimiento ASC';
                break;
            }
        }



        $oCon = new consulta();
        $oCon->setTipo('Multiple');

        $oCon->setQuery($query);
        $productos = $oCon->getData();
        unset($oCon);

        if ($tipo == 'Punto') {
            $this->buscarLotesPunto($productos, $id);
        } else if ($tipo == 'Bodega') {
            $this->buscarLotesBodega($productos, $id);
        }

        return $this->success($productos);
    }

    private function buscarLotesPunto($productos, $id)
    {
        foreach ($productos as $key => $producto) {
            # code...

            $query = 'SELECT I.Lote , SUM(I.Cantidad - (I.Cantidad_Apartada + I.Cantidad_Seleccionada)) AS Cantidad ,
                    I.Id_Producto ,
            
                      PRD.Nombre_Genaral as Nombre,
                
                
                    CONCAT("Lote :",I.Lote," - Cantidad :",SUM(I.Cantidad - (I.Cantidad_Apartada + I.Cantidad_Seleccionada))) AS label,
                    I.Id_Producto AS value,
                    IFNULL( (SELECT Costo_Promedio FROM Costo_Promedio WHERE Id_Producto = I.Id_Producto), 0) AS Costo,
                    FROM Inventario_Nuevo I 
                    INNER JOIN Producto PRD
                        On I.Id_Producto=PRD.Id_Producto   
                    WHERE I.Id_Punto_Dispensacion = "' . $id . '" AND I.Id_Producto = ' . $producto['Id_Producto'] . '
                    GROUP BY I.Id_Producto, I.Lote';

            $oCon = new consulta();
            $oCon->setTipo('Multiple');

            $oCon->setQuery($query);
            $lotes = $oCon->getData();
            unset($oCon);
            $productos[$key]['Lotes'] = $lotes;



        }
    }

    private function buscarLotesBodega($productos, $id)
    {
        foreach ($productos as $key => $producto) {
            # code...

            $query = 'SELECT I.Lote , SUM(I.Cantidad - (I.Cantidad_Apartada + I.Cantidad_Seleccionada)) AS Cantidad ,
                    I.Id_Producto ,
                    PRD.Nombre_General as Nombre,
                    CONCAT("Lote :",I.Lote," - Cantidad :",SUM(I.Cantidad - (I.Cantidad_Apartada + I.Cantidad_Seleccionada))) AS label,
                    I.Id_Producto AS value,
                    IFNULL(C.Costo_Promedio,0) AS Costo,
                    FROM Inventario_Nuevo I 
                    INNER JOIN Producto PRD
                    On I.Id_Producto = PRD.Id_Producto   
                    LEFT JOIN Costo_Promedio C
                    ON C.Id_Producto = I.Id_Producto
                    WHERE I.Id_Estiba = "' . $id . '" AND I.Id_Producto = ' . $producto['Id_Producto'] . '
                    GROUP BY I.Id_Producto, I.Lote';

            $oCon = new consulta();
            $oCon->setTipo('Multiple');

            $oCon->setQuery($query);
            $lotes = $oCon->getData();
            unset($oCon);
            $productos[$key]['Lotes'] = $lotes;

        }
    }

    public function movimientosAjusteIndividual()
    {
        $id_registro = (isset($_REQUEST['id_registro']) ? $_REQUEST['id_registro'] : '');
        $id_funcionario_imprime = (isset($_REQUEST['id_funcionario_elabora']) ? $_REQUEST['id_funcionario_elabora'] : '');
        $tipo_valor = (isset($_REQUEST['tipo_valor']) ? $_REQUEST['tipo_valor'] : '');
        $titulo = $tipo_valor != '' ? "CONTABILIZACIÓN NIIF" : "CONTABILIZACIÓN PCGA";

        $queryObj = new QueryBaseDatos();

        $oItem = new complex('Ajuste_Individual', 'Id_Ajuste_Individual', $id_registro);
        $datos = $oItem->getData();
        unset($oItem);

        $query = '
        SELECT
        PC.Codigo,
        PC.Nombre,
        PC.Codigo_Niif,
        PC.Nombre_Niif,
        MC.Nit,
        MC.Fecha_Movimiento AS Fecha,
        MC.Tipo_Nit,
        MC.Id_Registro_Modulo,
        MC.Documento,
        MC.Debe,
        MC.Haber,
        MC.Debe_Niif,
        MC.Haber_Niif,
            (CASE
                WHEN MC.Tipo_Nit = "Cliente" THEN (SELECT IFNULL(third_parties.social_reason, CONCAT_WS(" ", third_parties.first_name, third_parties.first_surname)) FROM third_parties WHERE nit = MC.Nit)
                WHEN MC.Tipo_Nit = "Proveedor" THEN (SELECT IFNULL(third_parties.social_reason, CONCAT_WS(" ", third_parties.first_name, third_parties.first_surname)) FROM third_parties WHERE nit = MC.Nit)
                WHEN MC.Tipo_Nit = "Funcionario" THEN (SELECT full_name FROM people WHERE identifier = MC.Nit)
            END) AS Nombre_Cliente,
            "Ajuste Individual" AS Registro
        FROM Movimiento_Contable MC
        INNER JOIN Plan_Cuentas PC ON MC.Id_Plan_Cuenta = PC.Id_Plan_Cuentas
        WHERE
            MC.Estado = "Activo" AND Id_Modulo = 8 AND Id_registro_Modulo =' . $id_registro . ' ORDER BY Debe DESC';

        $queryObj->SetQuery($query);
        $movimientos = $queryObj->ExecuteQuery('multiple');


        $query = '
        SELECT
        SUM(MC.Debe) AS Debe,
        SUM(MC.Haber) AS Haber,
        SUM(MC.Debe_Niif) AS Debe_Niif,
        SUM(MC.Haber_Niif) AS Haber_Niif
        FROM Movimiento_Contable MC
        WHERE
            MC.Estado = "Activo" AND Id_Modulo = 8 AND Id_registro_Modulo =' . $id_registro;

        $queryObj->SetQuery($query);
        $movimientos_suma = $queryObj->ExecuteQuery('simple');

        $query = '
        SELECT
            full_name AS Nombre_Funcionario
        FROM people
        WHERE
            identifier =' . $id_funcionario_imprime;

        $queryObj->SetQuery($query);
        $imprime = $queryObj->ExecuteQuery('simple');

        $elabora = Person::where('identifier', $datos["Identificacion_Funcionario"])
            ->value(DB::raw("IFNULL(CONCAT_WS(' ', first_name, first_surname), 'Nombre no disponible')"));

        $header = (object) [
            'Titulo' => $titulo,
            'Codigo' => $datos['Codigo'] ?? '',
            'Fecha' => $datos['Fecha'],
            'CodigoFormato' => $datos['Codigo'] ?? '',
        ];

        $pdf = Pdf::loadView('pdf.movimientos_ajuste_individual', [
            'datos' => $datos,
            'datosCabecera' => $header,
            'elabora' => $elabora,
            'tipo_valor' => $tipo_valor,
            'movimientos' => $movimientos,
            'movimientos_suma' => $movimientos_suma,
            'imprime' => $imprime,
        ]);

        return $pdf->stream("movimientosAjusteIndividual");
    }

    public function actividadesAjusteIndividual()
    {
        $id_ajuste = isset($_REQUEST['Id_Ajuste']) ? $_REQUEST['Id_Ajuste'] : false;

        if ($id_ajuste) {

            $query = "SELECT A.Fecha_Creacion AS Fecha, A.Estado, FC.full_name AS Funcionario, 
                FC.image as Imagen, A.Detalle AS Detalles
                FROM Actividad_Ajuste_Individual A 
                INNER JOIN people FC ON FC.id = A.Identificacion_Funcionario 
                WHERE A.Id_Ajuste_Individual = $id_ajuste";

            $oCon = new consulta();
            $oCon->setQuery($query);
            $oCon->setTipo('Multiple');
            $res['data'] = $oCon->getData();
            $res['type'] = 'success';
            return $this->success($res);
        }
    }
}
