<?php

namespace App\Http\Controllers;

use App\Exports\ProductosExport;
use App\Http\Services\complex;
use App\Http\Services\consulta;
use App\Http\Services\HttpResponse;
use App\Http\Services\QueryBaseDatos;
use App\Http\Services\Utility;
use App\Http\Services\Contabilizar;
use App\Http\Services\PaginacionData;
use App\Models\DocInventarioAuditable;
use App\Models\DocInventarioFisico;
use App\Models\Estiba;
use App\Models\InventarioFisicoNuevo;
use App\Models\InventarioNuevo;
use App\Models\People;
use App\Models\Product;
use App\Traits\ApiResponser;
use Illuminate\Support\Facades\DB;
use App\Models\Producto;
use App\Models\ProductoDocInventarioFisico;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use stdClass;
use Carbon\Carbon as Carbon;
use DateTime;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class InventarioNuevoController extends Controller
{
    use ApiResponser;

    public function listar2(Request $request)
    {
        $sin_inventario = $request->get('sin_inventario', '');
        $condicion_sin_inventario = '';
        if ($sin_inventario == "false") {
            $condicion_sin_inventario = " AND (I.Cantidad - I.Cantidad_Apartada - I.Cantidad_Seleccionada) > 0";
        } else if ($sin_inventario == "true") {
            $condicion_sin_inventario = "";
        } else if ($sin_inventario == "") {
            $condicion_sin_inventario = "";
        }
        $condicion = '';
        if (isset($_REQUEST['grupo']) && $_REQUEST['grupo'] != "") {
            $condicion .= " AND GE.Nombre LIKE '%$_REQUEST[grupo]%'";
        }
        if (isset($_REQUEST['bod']) && $_REQUEST['bod'] != "") {
            $condicion .= " AND b.Nombre LIKE '%$_REQUEST[bod]%'";
        }
        if (isset($_REQUEST['cant']) && $_REQUEST['cant'] != "") {
            $condicion .= " AND (I.Cantidad-I.Cantidad_Apartada-I.Cantidad_Seleccionada)=$_REQUEST[cant]";
        }
        if (isset($_REQUEST['cant_apar']) && $_REQUEST['cant_apar'] != "") {
            $condicion .= " AND I.Cantidad_Apartada=$_REQUEST[cant_apar]";
        }
        if (isset($_REQUEST['cant_sel']) && $_REQUEST['cant_sel'] != "") {
            $condicion .= " AND I.Cantidad_Seleccionada=$_REQUEST[cant_sel]";
        }
        if (isset($_REQUEST['costo']) && $_REQUEST['costo'] != "") {
            $condicion .= " AND I.Costo=$_REQUEST[costo]";
        }
        if (isset($_REQUEST['invima']) && $_REQUEST['invima'] != "") {
            $condicion .= " AND PRD.Invima LIKE '%$_REQUEST[invima]%'";
        }
        if (isset($_REQUEST['iva']) && $_REQUEST['iva'] != "") {
            $condicion .= " AND PRD.Gravado='$_REQUEST[iva]'";
        }

        $condicion_principal = '';
        if (isset($_REQUEST['id']) && ($_REQUEST['id'] != "" && $_REQUEST['id'] != "0")) {
            $condicion_principal = " WHERE SubC.Id_Categoria_Nueva=" . $_REQUEST['id'];
        } else {
            $condicion_principal = " WHERE SubC.Id_Categoria_Nueva!=0";
        }
        if (isset($_REQUEST['id_bodega_nuevo']) && ($_REQUEST['id_bodega_nuevo'] != "" && $_REQUEST['id_bodega_nuevo'] != "0")) {
            $condicion_principal .= ' AND B.Id_Bodega_Nuevo =' . $_REQUEST['id_bodega_nuevo'];
        } else {
            $condicion_principal .= ' AND B.Id_Bodega_Nuevo != 0';
        }

        $query = 'SELECT
            GROUP_CONCAT(I.Id_Inventario_Nuevo) AS Id_Inventario_Nuevo
            FROM Inventario_Nuevo I
            INNER JOIN Producto PRD ON I.Id_Producto = PRD.Id_Producto
            INNER JOIN Estiba E ON E.Id_Estiba = I.Id_Estiba
            INNER JOIN Grupo_Estiba GE ON GE.Id_Grupo_Estiba = E.Id_Grupo_Estiba
            INNER JOIN Bodega_Nuevo B ON B.Id_Bodega_Nuevo = E.Id_Bodega_Nuevo
            INNER JOIN Subcategoria SubC ON PRD.Id_Subcategoria = SubC.Id_Subcategoria
            INNER JOIN Categoria_Nueva C ON SubC.Id_Categoria_Nueva = C.Id_Categoria_Nueva
            ' . $condicion_principal . ' ' . $condicion . $condicion_sin_inventario . '
            GROUP BY B.Id_Bodega_Nuevo,
            I.Id_Producto ';

        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->setTipo('Multiple');
        $total = $oCon->getData();
        unset($oCon);
        $total = count($total);

        ####### PAGINACIÓN ########
        $tamPag = $_REQUEST['pageSize'] ?? 20;
        $numReg = $total;
        $paginas = ceil($numReg / $tamPag);
        $limit = "";
        $paginaAct = "";
        if (!isset($_REQUEST['pag']) || $_REQUEST['pag'] == '') {
            $paginaAct = 1;
            $limit = 0;
        } else {
            $paginaAct = $_REQUEST['pag'];
            $limit = ($paginaAct - 1) * $tamPag;
        }
        if ($sin_inventario == "false") {
            $condicion_sin_inventario = " AND (I.Cantidad-I.Cantidad_Apartada-I.Cantidad_Seleccionada) > 0";
        }
        $listaLeft = '';
        $listaSelect = '';
        if (isset($_REQUEST['lista']) && $_REQUEST['lista'] != '') {
            $listaLeft = '';
            $listaSelect = ' 0 AS Precio_Lista ';
        } else {
            $listaSelect = ' I.Costo AS Precio_Lista ';
        }

        $query = 'SELECT
            SUM(IFNULL((IC.Cantidad  ),0 )) AS cantidadContrato,
            GROUP_CONCAT(I.Id_Inventario_Nuevo) AS Id_Inventario_Nuevo,
            I.Cantidad,
            GE.Nombre AS Nombre_Grupo,
            I.Id_Producto,
            B.Id_Bodega_Nuevo,
            SUM(I.Cantidad_Apartada) AS Cantidad_Apartada,
            SUM(I.Cantidad_Seleccionada) AS Cantidad_Seleccionada,
            PRD.Nombre_General AS Nombre_Producto,
            (SELECT CPM.Costo_Promedio FROM Costo_Promedio CPM WHERE CPM.Id_Producto = PRD.Id_Producto) AS Costo,
            SUM(IF((I.Cantidad - I.Cantidad_Apartada - I.Cantidad_Seleccionada ) < 0, 0, (I.Cantidad - I.Cantidad_Apartada - I.Cantidad_Seleccionada)) ) AS Cantidad_Disponible,
            C.Nombre AS Nombre_Categoria,
            GROUP_CONCAT(CONCAT (" Estiba ", E.Nombre , "  : ",
                IF(
                    (I.Cantidad - I.Cantidad_Apartada - I.Cantidad_Seleccionada) < 0,
                    0, (I.Cantidad - I.Cantidad_Apartada - I.Cantidad_Seleccionada)
                )
            )) AS "Nombre_Estiba",
            ' . $listaSelect . '
            FROM
            Inventario_Nuevo I
            LEFT JOIN Inventario_Contrato IC ON I.Id_Inventario_Nuevo = IC.Id_Inventario_Nuevo
            INNER JOIN Producto PRD ON I.Id_Producto = PRD.Id_Producto
            INNER JOIN Estiba E ON E.Id_Estiba = I.Id_Estiba
            INNER JOIN Grupo_Estiba GE ON GE.Id_Grupo_Estiba = E.Id_Grupo_Estiba
            INNER JOIN Bodega_Nuevo B ON B.Id_Bodega_Nuevo = E.Id_Bodega_Nuevo
            INNER JOIN Subcategoria SubC ON PRD.Id_Subcategoria = SubC.Id_Subcategoria
            INNER JOIN Categoria_Nueva C ON SubC.Id_Categoria_Nueva = C.Id_Categoria_Nueva
            ' . $listaLeft;

        $query .= $condicion_principal . ' ' . $condicion . $condicion_sin_inventario . '
        GROUP BY B.Id_Bodega_Nuevo, IC.Id_Inventario_Nuevo, I.Id_Producto' .
            ' ORDER BY PRD.Nombre_Comercial LIMIT ' . $limit . ',' . $tamPag;

        $oCon = new consulta();
        $oCon->setTipo('Multiple');
        $oCon->setQuery($query);
        $inventario['inventarios'] = $oCon->getData();
        unset($oCon);
        $i = -1;
        $inventario['numReg'] = $numReg;
        return response()->json($inventario);
    }

    public function listar(Request $request)
    {
        $query = InventarioNuevo::selectRaw('
            SUM(IFNULL((IC.Cantidad), 0)) AS cantidadContrato,
            GROUP_CONCAT(I.Id_Inventario_Nuevo) AS Id_Inventario_Nuevo,
            I.Cantidad,
            GE.Nombre AS Nombre_Grupo,
            I.Id_Producto,
            B.Id_Bodega_Nuevo,
            SUM(I.Cantidad_Apartada) AS Cantidad_Apartada,
            SUM(I.Cantidad_Seleccionada) AS Cantidad_Seleccionada,
            PRD.Nombre_General,
            PRD.Nombre_Comercial,
            PRD.Referencia,
            (SELECT CPM.Costo_Promedio FROM Costo_Promedio CPM WHERE CPM.Id_Producto = PRD.Id_Producto) AS Costo,
            SUM(IF((I.Cantidad - I.Cantidad_Apartada - I.Cantidad_Seleccionada) < 0, 0, (I.Cantidad - I.Cantidad_Apartada - I.Cantidad_Seleccionada))) AS Cantidad_Disponible,
            C.Nombre AS Nombre_Categoria,
            GROUP_CONCAT(CONCAT(" Estiba ", E.Nombre, ": ", IF((I.Cantidad - I.Cantidad_Apartada - I.Cantidad_Seleccionada) < 0, 0, (I.Cantidad - I.Cantidad_Apartada - I.Cantidad_Seleccionada)))) AS Nombre_Estiba
        ')->from('Inventario_Nuevo as I')
            ->leftJoin('Inventario_Contrato as IC', 'I.Id_Inventario_Nuevo', '=', 'IC.Id_Inventario_Nuevo')
            ->join('Producto as PRD', 'I.Id_Producto', '=', 'PRD.Id_Producto')
            ->rightJoin('Estiba as E', 'E.Id_Estiba', '=', 'I.Id_Estiba')
            ->join('Grupo_Estiba as GE', 'GE.Id_Grupo_Estiba', '=', 'E.Id_Grupo_Estiba')
            ->join('Bodega_Nuevo as B', 'B.Id_Bodega_Nuevo', '=', 'E.Id_Bodega_Nuevo')
            ->join('Subcategoria as SubC', 'PRD.Id_Subcategoria', '=', 'SubC.Id_Subcategoria')
            ->join('Categoria_Nueva as C', 'SubC.Id_Categoria_Nueva', '=', 'C.Id_Categoria_Nueva');

        if ($request->has('sin_inventario') && $request->sin_inventario == 'false') {
            $query->whereRaw('(I.Cantidad - I.Cantidad_Apartada - I.Cantidad_Seleccionada) > 0');
        }

        if ($request->filled('grupo')) {
            $query->where('GE.Nombre', 'LIKE', '%' . $request->grupo . '%');
        }

        if ($request->filled('bod')) {
            $query->where('B.Nombre', 'LIKE', '%' . $request->bod . '%');
        }

        if ($request->filled('cant')) {
            $query->whereRaw('(I.Cantidad - I.Cantidad_Apartada - I.Cantidad_Seleccionada) = ?', [$request->cant]);
        }

        if ($request->filled('cant_apar')) {
            $query->where('I.Cantidad_Apartada', $request->cant_apar);
        }

        if ($request->filled('cant_sel')) {
            $query->where('I.Cantidad_Seleccionada', $request->cant_sel);
        }

        if ($request->filled('costo')) {
            $query->where('I.Costo', $request->costo);
        }

        if ($request->filled('invima')) {
            $query->where('PRD.Invima', 'LIKE', '%' . $request->invima . '%');
        }

        if ($request->filled('iva')) {
            $query->where('PRD.Gravado', $request->iva);
        }

        if ($request->filled('id') && $request->id != 0) {
            $query->where('SubC.Id_Categoria_Nueva', $request->id);
        } else {
            $query->where('SubC.Id_Categoria_Nueva', '!=', 0);
        }

        if ($request->filled('id_bodega_nuevo') && $request->id_bodega_nuevo != 0) {
            $query->where('B.Id_Bodega_Nuevo', $request->id_bodega_nuevo);
        } else {
            $query->where('B.Id_Bodega_Nuevo', '!=', 0);
        }

        $pageSize = $request->input('pageSize', 20);
        $currentPage = $request->input('pag', 1);
        $offset = ($currentPage - 1) * $pageSize;

        if ($request->filled('lista')) {
            $query->selectRaw('0 AS Precio_Lista');
        } else {
            $query->selectRaw('I.Costo AS Precio_Lista');
        }

        $query->groupBy('B.Id_Bodega_Nuevo', 'IC.Id_Inventario_Nuevo', 'I.Id_Producto')
            ->orderBy('PRD.Nombre_Comercial')
            ->offset($offset)
            ->limit($pageSize);

        $inventarios = $query->paginate($pageSize, ['*'], 'pag', $currentPage);

        return $this->success($inventarios);
    }



    public function iniciarInventario(Request $request)
    {
        $idEstiba = $request->input('Id_Estiba', '');
        $codigoBarras = $request->input('Codigo_Barras', '');
        $contador = $request->input('Contador', '');
        $digitador = $request->input('Digitador', '');
        $tipo = $request->input('Tipo', '');


        // Condicional para verificar si Id_Estiba no es 0
        $cond = ($idEstiba != "0" && $idEstiba != 0) ? ' AND PRD.Id_Categoria =' . $idEstiba : '';

        // Consulta para verificar si hay un inventario físico en curso para la estiba dada
        $invFisico = DocInventarioFisico::whereHas('estiba', function ($query) use ($codigoBarras) {
            $query->where('Codigo_Barras', $codigoBarras);
        })->where('Estado', '!=', 'Terminado')->first();

        // Consulta para verificar si hay un inventario auditable en curso para la estiba dada
        $invAuditable = DocInventarioAuditable::whereHas('bodegaNuevo.estibas', function ($query) use ($codigoBarras) {
            $query->where('Codigo_Barras', $codigoBarras);
        })->where('Estado', '!=', 'Terminado')->first();

        // Determinar si existe un inventario en curso (físico o auditable)
        $inv = $invFisico ?? $invAuditable;

        // Preparar la condición de búsqueda para el nombre comercial
        $letras = '';
        $let = explode("-", $letras ?? '');
        $order = 'PRD.Nombre_Comercial';
        $fin = '';

        foreach ($let as $l) {
            $fin .= $order . ' LIKE "' . $l . '%" OR ';
        }
        $fin = trim($fin, " OR ");
        if ($fin != '') {
            $cond .= ' AND (' . $fin . ') AND I.Cantidad>0 GROUP BY I.Id_Producto';
        }

        $inicio = Carbon::now()->format('Y-m-d H:i:s'); // Obtener la fecha y hora actual

        // Verificar si no existe un inventario en curso
        if (!$inv) {
            // Consultar información de los funcionarios por sus identificadores
            $funcContador = People::where('identifier', $contador)->first();
            $funcDigitador = People::where('identifier', $digitador)->first();

            // Verificar que ambos funcionarios existen en el sistema
            if ($funcContador && $funcDigitador) {
                // Condicional para el tipo de inventario (Punto o Bodega)
                $condEstiba = '';
                if ($tipo == 'Punto') {
                    $condEstiba = 'Id_Punto_Dispensacion IS NOT NULL AND Id_Punto_Dispensacion != ""';
                } else {
                    $condEstiba = 'Id_Bodega_Nuevo IS NOT NULL AND Id_Bodega_Nuevo != ""';
                }

                // Consulta para obtener la estiba por código de barras y condición
                $estiba = Estiba::where('Codigo_Barras', $codigoBarras)
                    ->whereRaw($condEstiba)
                    ->first();

                if ($estiba) {
                    // Apartar la estiba para el inventario actualizando su estado
                    $estiba->update(['Estado' => 'Inventario']);
                    $code = generateConsecutive('Doc_Inventario_Fisico');
                    // Crear el documento de inventario físico
                    $docInventarioFisico = new DocInventarioFisico();
                    $docInventarioFisico->Fecha_Inicio = $inicio;
                    $docInventarioFisico->Codigo = $code;
                    $docInventarioFisico->Funcionario_Digita = $funcDigitador->id;
                    $docInventarioFisico->Funcionario_Cuenta = $funcContador->id;
                    $docInventarioFisico->Id_Estiba = $estiba->Id_Estiba;
                    $docInventarioFisico->Estado = 'Pendiente Primer Conteo';
                    $docInventarioFisico->save();
                    sumConsecutive('Doc_Inventario_Fisico');
                    // Guardar historial dependiendo del tipo de inventario
                    if ($tipo == 'Punto') {
                        $this->guardarHistorialPunto($docInventarioFisico->Id_Doc_Inventario_Fisico, $estiba->Id_Estiba);
                    } else {
                        $this->guardarHistorial($docInventarioFisico->Id_Doc_Inventario_Fisico, $estiba->Id_Estiba);
                    }

                    // Preparar la respuesta de éxito
                    $resultado = [
                        "Id_Doc_Inventario_Fisico" => $docInventarioFisico->Id_Doc_Inventario_Fisico,
                        "Funcionario_Digita" => $funcDigitador,
                        "Funcionario_Cuenta" => $funcContador,
                        "Estiba" => $estiba,
                        "Inicio" => $inicio,
                        "Tipo" => "success",
                        "Title" => "Inventario iniciado correctamente",
                        "Text" => "Vamos a dar inicio al inventario físico."
                    ];
                } else {
                    //error si la estiba no se encuentra
                    $resultado = [
                        "Tipo" => "error",
                        "Title" => "Error de estiba",
                        "Text" => "El código de barras de la estiba no coincide con los códigos registrados en el sistema."
                    ];
                }
            } else {
                //error si los funcionarios no coinciden
                $resultado = [
                    "Tipo" => "error",
                    "Title" => "Error de funcionario",
                    "Text" => "Alguno de los números de documento de los funcionarios no coincide con funcionarios registrados en el sistema."
                ];
            }
        } else {
            //error si ya existe un inventario en curso
            $resultado = [
                "Tipo" => "error",
                "Title" => "Inventario no iniciado",
                "Text" => "Ya hay otro grupo de personas trabajando en un inventario para la misma estiba."
            ];
        }

        return response()->json($resultado);
    }

    // Función para guardar el historial del inventario
    function guardarHistorial($Id_Doc_Inv, $idEstiba)
    {
        // Consulta para obtener los datos del inventario nuevo para la estiba dada
        $query = 'SELECT * FROM Inventario_Nuevo I WHERE I.Id_Estiba = ' . $idEstiba;

        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->setTipo('Multiple');
        $inventario = $oCon->getData();

        // Iterar sobre los elementos del inventario y guardarlos en el historial
        foreach ($inventario as $item) {
            $oItem = new complex('Historial_Inventario', 'Id_Historial_Inventario');
            $oItem->Id_Inventario_Nuevo = $item->Id_Inventario_Nuevo;
            $oItem->Id_Estiba = $item->Id_Estiba;
            $oItem->Cantidad = $item->Cantidad;
            $oItem->Cantidad_Apartada = $item->Cantidad_Apartada;
            $oItem->Cantidad_Seleccionada = $item->Cantidad_Seleccionada;
            $oItem->Id_Doc_Inventario_Fisico = $Id_Doc_Inv;
            $oItem->Id_Producto = $item->Id_Producto;
            $oItem->save();
            unset($oItem);
        }
    }

    // Función para guardar el historial del inventario de punto
    function guardarHistorialPunto($Id_Doc_Inv, $idEstiba)
    {
        // Consulta para obtener los datos del inventario nuevo para la estiba dada
        $query = 'SELECT * FROM Inventario_Nuevo I WHERE I.Id_Estiba = ' . $idEstiba;

        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->setTipo('Multiple');
        $inventario = $oCon->getData();

        // Iterar sobre los elementos del inventario y guardarlos en el historial de punto
        foreach ($inventario as $item) {
            $oItem = new complex('Historial_Inventario_Punto', 'Historial_Inventario_Punto');
            $oItem->Id_Inventario_Nuevo = $item->Id_Inventario_Nuevo;
            $oItem->Id_Estiba = $item->Id_Estiba;
            $oItem->Cantidad = $item->Cantidad;
            $oItem->Cantidad_Apartada = $item->Cantidad_Apartada;
            $oItem->Cantidad_Seleccionada = $item->Cantidad_Seleccionada;
            $oItem->Id_Doc_Inventario_Fisico_Punto = $Id_Doc_Inv;
            $oItem->Id_Producto = $item->Id_Producto;
            $oItem->save();
            unset($oItem);
        }
    }

    public function documentosIniciados()
    {


        $docsInventarioFisico = DocInventarioFisico::select(
            'Doc_Inventario_Fisico.Id_Doc_Inventario_Fisico',
            'Doc_Inventario_Fisico.Funcionario_Digita AS Id_Funcionario_Digita',
            'Doc_Inventario_Fisico.Funcionario_Cuenta AS Id_Funcionario_Cuenta',
            'Doc_Inventario_Fisico.Fecha_Inicio',
            'Doc_Inventario_Fisico.Estado',
            'Doc_Inventario_Fisico.Id_Estiba',
            'E.Nombre AS Estiba',
            'B.Nombre AS Bodega',
            'FD.first_name AS Funcionario_Digita_Nombres',
            'FD.first_surname AS Funcionario_Digita_Apellidos',
            'FC.first_name AS Funcionario_Cuenta_Nombres',
            'FC.first_surname AS Funcionario_Cuenta_Apellidos',
            DB::raw("'General' AS Tipo")
        )
            ->join('Estiba as E', 'E.Id_Estiba', '=', 'Doc_Inventario_Fisico.Id_Estiba')
            ->join('Bodega_Nuevo as B', 'B.id_Bodega_Nuevo', '=', 'E.Id_Bodega_Nuevo')
            ->join(DB::raw('(SELECT F.id, F.first_name, F.first_surname FROM people F) as FD'), 'FD.id', '=', 'Doc_Inventario_Fisico.Funcionario_Digita')
            ->join(DB::raw('(SELECT F.id, F.first_name, F.first_surname FROM people F) as FC'), 'FC.id', '=', 'Doc_Inventario_Fisico.Funcionario_Cuenta')
            ->whereNotIn('Doc_Inventario_Fisico.Estado', ['Terminado'])
            ->orderByDesc('Doc_Inventario_Fisico.Fecha_Inicio')
            ->paginate(request()->get('pageSize', 10), ['*'], 'page', request()->get('page', 1));


        $docsInventarioAuditable = DocInventarioAuditable::select(
            'Doc_Inventario_Auditable.Id_Doc_Inventario_Auditable As Id_Doc_Inventario_Fisico',
            'Doc_Inventario_Auditable.Funcionario_Digita AS Id_Funcionario_Digita',
            'Doc_Inventario_Auditable.Funcionario_Cuenta AS Id_Funcionario_Cuenta',
            'Doc_Inventario_Auditable.Fecha_Inicio',
            'Doc_Inventario_Auditable.Estado',
            'B.Nombre AS Bodega',
            'B.id_Bodega_Nuevo',
            'FD.first_name AS Funcionario_Digita_Nombres',
            'FD.first_surname AS Funcionario_Digita_Apellidos',
            'FC.first_name AS Funcionario_Cuenta_Nombres',
            'FC.first_surname AS Funcionario_Cuenta_Apellidos',
            DB::raw("'Auditoria' AS Tipo")
        )
            ->join('Bodega_Nuevo as B', 'B.id_Bodega_Nuevo', '=', 'Doc_Inventario_Auditable.Id_Bodega')
            ->join(DB::raw('(SELECT F.id, F.first_name, F.first_surname FROM people F) as FD'), 'FD.id', '=', 'Doc_Inventario_Auditable.Funcionario_Digita')
            ->join(DB::raw('(SELECT F.id, F.first_name, F.first_surname FROM people F) as FC'), 'FC.id', '=', 'Doc_Inventario_Auditable.Funcionario_Cuenta')
            ->whereNotIn('Doc_Inventario_Auditable.Estado', ['Terminado'])
            ->orderByDesc('Doc_Inventario_Auditable.Fecha_Inicio')
            ->get();


        return $this->success($docsInventarioFisico);

    }
    public function getInventario()
    {
        $idDocInventarioFisico = request('Id_Doc_Inventario_Fisico');

        if ($idDocInventarioFisico) {
            $inventario = DocInventarioFisico::query()
                ->select(
                    'Doc_Inventario_Fisico.Id_Doc_Inventario_Fisico',
                    'Doc_Inventario_Fisico.Estado',
                    'Doc_Inventario_Fisico.Funcionario_Digita',
                    'Doc_Inventario_Fisico.Funcionario_Cuenta',
                    'Doc_Inventario_Fisico.Fecha_Inicio',
                    'Doc_Inventario_Fisico.Codigo',
                    'Doc_Inventario_Fisico.Lista_Productos',
                    'Estiba.Nombre AS Nombre_Estiba',
                    'Estiba.Id_Estiba',
                    'Bodega_Nuevo.Nombre AS Nombre_Bodega',
                    'Bodega_Nuevo.Id_Bodega_Nuevo'
                )
                ->join('Estiba', 'Estiba.Id_Estiba', '=', 'Doc_Inventario_Fisico.Id_Estiba')
                ->leftJoin('Bodega_Nuevo', 'Bodega_Nuevo.Id_Bodega_Nuevo', '=', 'Estiba.Id_Bodega_Nuevo')
                ->where('Doc_Inventario_Fisico.Id_Doc_Inventario_Fisico', $idDocInventarioFisico)
                ->first();

            if ($inventario) {
                $funcionarioDigita = People::find($inventario->Funcionario_Digita);
                $funcionarioCuenta = People::find($inventario->Funcionario_Cuenta);

                $id = (isset($_REQUEST['Id_Doc_Inventario_Fisico']) ? $_REQUEST['Id_Doc_Inventario_Fisico'] : '');
                $response = array();
                $http_response = new HttpResponse();

                // Obtener el documento existente
                $oItem = new complex("Doc_Inventario_Fisico", "Id_Doc_Inventario_Fisico", $id);

                // Verificar el contenido de Lista_Productos
                $listaProductosJson = $oItem->Lista_Productos;

                // Eliminar caracteres de control del JSON
                $listaProductosJson = preg_replace('/[[:cntrl:]]/', '', $listaProductosJson);

                // Decodificar el JSON
                $productosArray = json_decode($listaProductosJson, true);

                if ($productosArray === null) {
                    $productosArray = []; // Manejar lista vacía si hay error en la decodificación JSON
                }

                $productosIds = array_column($productosArray, 'Id_Producto');

                // Obtener productos con sus variables y etiquetas asociadas
                list($productos, $variablesLabels) = $this->obtenerProductosConVariables($productosIds);

                $productosData = [];
                $productosMap = [];
                foreach ($productosArray as $productoArray) {
                    $productoId = $productoArray['Id_Producto'];
                    if (!isset($productosMap[$productoId])) {
                        $producto = $productos->firstWhere('Id_Producto', $productoId);

                        if ($producto) {
                            $productoData = $producto->toArray();
                            $productoData['Variables'] = $producto->variables;
                            $productoData['VariablesLabels'] = array_keys($producto->variables);

                            // Filtrar lotes con valores nulos o vacíos y agruparlos al final
                            if (isset($productoArray['Lotes']) && is_array($productoArray['Lotes'])) {
                                $lotesValidos = [];
                                $lotesInvalidos = [];

                                foreach ($productoArray['Lotes'] as $lote) {
                                    if (!empty($lote['Lote']) && !empty($lote['Fecha_Vencimiento']) && !empty($lote['Cantidad_Encontrada'])) {
                                        $lotesValidos[] = $lote;
                                    } else {
                                        $lotesInvalidos[] = $lote;
                                    }
                                }

                                // Agregar un lote vacío al final
                                $loteVacio = [
                                    'Id_Producto' => $productoId,
                                    'Lote' => null,
                                    'Fecha_Vencimiento' => null,
                                    'Cantidad_Encontrada' => null,
                                    'Codigo' => 'Ingresa uno nuevo u279c',
                                    'Id_Inventario_Nuevo' => 0,
                                    'Cantidad' => 0,
                                    'Cantidad_Final' => ''
                                ];

                                $productoData['Lotes'] = array_merge($lotesValidos, $lotesInvalidos, [$loteVacio]);
                            }

                            // Agregar fecha de vencimiento solo si no es null
                            if (isset($productoArray['Fecha_Vencimiento']) && $productoArray['Fecha_Vencimiento'] !== null) {
                                $productoData['Fecha_Vencimiento'] = $productoArray['Fecha_Vencimiento'];
                            }

                            // Agregar cantidad encontrada
                            $productoData['Cantidad_Encontrada'] = isset($productoArray['Cantidad_Encontrada']) ? $productoArray['Cantidad_Encontrada'] : null;

                            $productosMap[$productoId] = $productoData;
                        }
                    }
                }

                $productosData = array_values($productosMap);

                $resultado['Data']['Id_Doc_Inventario_Fisico'] = $inventario->Id_Doc_Inventario_Fisico;
                $resultado['Data']['Funcionario_Digita'] = $funcionarioDigita;
                $resultado['Data']['Funcionario_Cuenta'] = $funcionarioCuenta;
                $resultado['Data']['Productos'] = $productosData;
                $resultado['Data']['Estiba']['Nombre'] = $inventario->Nombre_Estiba;
                $resultado['Data']['Estiba']['Id_Estiba'] = $inventario->Id_Estiba;
                $resultado['Data']['Bodega']['Nombre'] = $inventario->Nombre_Bodega;
                $resultado['Data']['Bodega']['Id_Bodega_Nuevo'] = $inventario->Id_Bodega_Nuevo;
                $resultado['Data']['Inicio'] = $inventario->Fecha_Inicio;
                $resultado['Data']['Estado'] = $inventario->Estado;
                $resultado['Data']['Codigo'] = $inventario->Codigo;

                $resultado['Tipo'] = 'success';
                $resultado['Title'] = 'Inventario Iniciado Correctamente';
            } else {
                $resultado['Tipo'] = 'error';
                $resultado['Title'] = 'No se encontraron Inventarios';
            }
        } else {
            $resultado['Tipo'] = 'error';
            $resultado['Title'] = 'Debe ingresar un Inventario';
        }

        return response()->json($resultado);
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

        return [$productos, $variablesLabels];
    }

    public function consultaProducto(Request $request)
    {
        $id_estiba = $request->input('Id_Estiba', '');
        $codigo = $request->input('Codigo', '');

        $resultado = [];
        $condicion = $this->setCondiciones($codigo);

        $producto = Product::select(
            'Producto.Id_Producto',
            'Producto.Nombre_General as Nombre_Producto',
            'Producto.Nombre_General as Nombre',
            'Producto.Nombre_Comercial',
            'Producto.Unidad_Medida',
            DB::raw("IFNULL(Producto.Id_Categoria, 0) as Id_Categoria_Nueva"),
            DB::raw("(SELECT Nombre FROM Categoria_Nueva WHERE Id_Categoria_Nueva = Producto.Id_Categoria) as Categoria"),
            'Producto.Imagen',
            'Producto.Codigo_Barras'
        )
            ->leftJoin('Inventario_Nuevo as I', 'Producto.Id_Producto', '=', 'I.Id_Producto')
            ->whereRaw($condicion)
            ->groupBy('Producto.Id_Producto')
            ->first();

        if ($producto) {
            // Obtener productos con sus variables y etiquetas asociadas
            list($productos, $variablesLabels) = $this->obtenerProductosConVariables([$producto->Id_Producto]);

            // Asignar las variables y etiquetas al producto
            if (!empty($productos)) {
                $producto->Variables = $productos->first()->variables;
                $producto->VariablesLabels = $variablesLabels;
            }

            // Verificar si la categoría del producto tiene lote y/o fecha de vencimiento
            $categoria = DB::table('Categoria_Nueva')
                ->select('has_lote', 'has_expiration_date')
                ->where('Id_Categoria_Nueva', $producto->Id_Categoria_Nueva)
                ->first();

            if ($categoria) {
                if ($categoria->has_lote) {
                    $producto->Lotes = InventarioNuevo::select('Lote')->where('Id_Producto', $producto->Id_Producto)->first()->Lote ?? null;
                }
                if ($categoria->has_expiration_date) {
                    $producto->Fecha_Vencimiento = InventarioNuevo::select('Fecha_Vencimiento')->where('Id_Producto', $producto->Id_Producto)->first()->Fecha_Vencimiento ?? null;
                }
            }

            $producto->Mensaje = "Producto encontrado exitosamente.";
            $resultado["Tipo"] = "success";
            $resultado["Datos"] = $producto;
        } else {
            $resultado["Tipo"] = "error";
            $resultado["Titulo"] = "Producto No Encontrado";
            $resultado["Texto"] = "El Código de Barras Escaneado no coincide con ninguno de los productos que tenemos registrados.";
        }

        return response()->json($resultado);
    }



    private function setCondiciones($codigo)
    {
        $codigo1 = substr($codigo, 0, 12);
        return "Producto.Codigo_Barras = '$codigo' OR I.Codigo = '$codigo1' OR I.Alternativo LIKE '%$codigo1%'";
    }

    public function agregaProductos()
    {
        $queryObj = new QueryBaseDatos();
        $response = array();
        $http_response = new HttpResponse();
        $id = (isset($_REQUEST['Id_Doc_Inventario_Fisico']) ? $_REQUEST['Id_Doc_Inventario_Fisico'] : '');
        $productos = (isset($_REQUEST['Productos']) ? $_REQUEST['Productos'] : '');
        $prodNuevos = (array) json_decode($productos, true);

        // Obtener el documento existente
        $oItem = new complex("Doc_Inventario_Fisico", "Id_Doc_Inventario_Fisico", $id);

        // Decodificar la lista de productos existentes
        $listaProductosExistente = json_decode($oItem->Lista_Productos, true);

        // Verificar si la lista existente es nula o no es un array
        if (!is_array($listaProductosExistente)) {
            $listaProductosExistente = array();
        }

        // Crear un mapa de productos existentes por Id_Producto
        $productosExistentesMap = [];
        foreach ($listaProductosExistente as $prodExistente) {
            $productosExistentesMap[$prodExistente['Id_Producto']] = $prodExistente;
        }

        // Agregar o actualizar los productos nuevos en la lista existente
        foreach ($prodNuevos as &$prodNuevo) {
            $idProducto = $prodNuevo['Id_Producto'];

            // Si el producto ya existe en la lista existente
            if (isset($productosExistentesMap[$idProducto])) {
                // Actualizar Cantidad_Encontrada si está fuera de lotes
                if (isset($prodNuevo['Cantidad_Encontrada']) && $prodNuevo['Cantidad_Encontrada'] !== '') {
                    $productosExistentesMap[$idProducto]['Cantidad_Encontrada'] = $prodNuevo['Cantidad_Encontrada'];
                }

                // Reemplazar los lotes existentes con los nuevos lotes
                if (isset($prodNuevo['Lotes']) && is_array($prodNuevo['Lotes'])) {
                    $nuevosLotes = [];
                    foreach ($prodNuevo['Lotes'] as &$lote) {
                        // Validar y limpiar los campos Lote y Fecha_Vencimiento
                        if (!isset($lote['Lote']) || empty($lote['Lote'])) {
                            continue; // Skip empty lotes
                        } else {
                            $lote['Lote'] = strtoupper($lote['Lote']);
                        }

                        if (!isset($lote['Fecha_Vencimiento']) || empty($lote['Fecha_Vencimiento'])) {
                            $lote['Fecha_Vencimiento'] = null;
                        } else {
                            $lote['Fecha_Vencimiento'] = $this->validarFormatoFecha($lote['Fecha_Vencimiento']);
                        }

                        $nuevosLotes[] = $lote;
                    }
                    $productosExistentesMap[$idProducto]['Lotes'] = $nuevosLotes;
                }

            } else {
                // Si el producto no existe en la lista existente, agregarlo
                if (isset($prodNuevo['Lotes']) && is_array($prodNuevo['Lotes'])) {
                    $nuevosLotes = [];
                    foreach ($prodNuevo['Lotes'] as &$lote) {
                        // Validar y limpiar los campos Lote y Fecha_Vencimiento
                        if (!isset($lote['Lote']) || empty($lote['Lote'])) {
                            continue; // Skip empty lotes
                        } else {
                            $lote['Lote'] = strtoupper($lote['Lote']);
                        }

                        if (!isset($lote['Fecha_Vencimiento']) || empty($lote['Fecha_Vencimiento'])) {
                            $lote['Fecha_Vencimiento'] = null;
                        } else {
                            $lote['Fecha_Vencimiento'] = $this->validarFormatoFecha($lote['Fecha_Vencimiento']);
                        }

                        $nuevosLotes[] = $lote;
                    }
                    $prodNuevo['Lotes'] = $nuevosLotes;
                }

                // Validar y limpiar los campos Lote y Fecha_Vencimiento del producto
                if (!isset($prodNuevo['Lote']) || empty($prodNuevo['Lote'])) {
                    $prodNuevo['Lote'] = null;
                } else {
                    $prodNuevo['Lote'] = strtoupper($prodNuevo['Lote']);
                }

                if (!isset($prodNuevo['Fecha_Vencimiento']) || empty($prodNuevo['Fecha_Vencimiento'])) {
                    $prodNuevo['Fecha_Vencimiento'] = null;
                } else {
                    $prodNuevo['Fecha_Vencimiento'] = $this->validarFormatoFecha($prodNuevo['Fecha_Vencimiento']);
                }

                // Agregar el nuevo producto al mapa de productos existentes
                $productosExistentesMap[$idProducto] = $prodNuevo;
            }
        }

        // Convertir el mapa de productos existentes de nuevo a un array
        $listaProductosActualizada = array_values($productosExistentesMap);

        // Guardar la lista actualizada en el objeto
        $oItem->Lista_Productos = json_encode($listaProductosActualizada);
        $oItem->save();
        unset($oItem);

        $http_response->SetRespuesta(0, 'Operación exitosa', '¡Producto agregado exitosamente!');
        $response = $http_response->GetRespuesta();
        return response()->json($response);
    }



    public function gestionEstado()
    {
        $id_inventario_fisico = isset($_REQUEST['Id_Doc_Inventario_Fisico']) ? $_REQUEST['Id_Doc_Inventario_Fisico'] : false;
        $tipo = isset($_REQUEST['tipo_accion']) ? $_REQUEST['tipo_accion'] : false;

        // Cambiar el estado del inventario fisico
        $oItem = new complex('Doc_Inventario_Fisico', 'Id_Doc_Inventario_Fisico', $id_inventario_fisico);
        $oItem->Estado = $tipo;
        $band = $oItem->Id_Doc_Inventario_Fisico;
        $oItem->save();
        unset($oItem);
        if ($tipo != 'Haciendo Primer Conteo' || $tipo != 'Haciendo Segundo Conteo') {

            if ($band) {
                $resultado['titulo'] = "Operación exitosa";
                $resultado['mensaje'] = "Se ha guardado correctamente el inventario, puedes continuar en cualquier momento";
                $resultado['tipo'] = "success";
            } else {
                $resultado['titulo'] = "Error";
                $resultado['mensaje'] = "Ha ocurrido un error inesperado. Por favor verifica tu conexión a internet.";
                $resultado['tipo'] = "error";
            }
            return response()->json($resultado);
        }
    }

    public function ajustarInventario(Request $request)
    {
        $id_inventario_fisico = $request->input('Id_Doc_Inventario_Fisico', false);
        $productos = $request->input('productos', false);

        if ($productos) {
            $productos = is_string($productos) ? json_decode($productos, true) : (array) $productos;
        }

        foreach ($productos as $prod) {
            // Validar si el producto tiene una cantidad encontrada y actualizar inventario
            if (isset($prod['Cantidad_Encontrada']) && $prod['Cantidad_Encontrada'] > 0) {
                $oItem = new complex('Producto_Doc_Inventario_Fisico', 'Id_Producto_Doc_Inventario_Fisico');
                $oItem->Id_Producto = $prod['Id_Producto'];
                $oItem->Id_Inventario_Nuevo = !isset($prod['Id_Inventario_Nuevo']) || $prod['Id_Inventario_Nuevo'] == '' ? '0' : $prod['Id_Inventario_Nuevo'];
                $oItem->Primer_Conteo = $prod['Cantidad_Encontrada'];
                $oItem->Fecha_Primer_Conteo = date('Y-m-d');
                $oItem->Cantidad_Inventario = !isset($prod['Cantidad']) || $prod['Cantidad'] == '' ? '0' : $prod['Cantidad'];
                $oItem->Id_Doc_Inventario_Fisico = $id_inventario_fisico;

                // Asignar Lote y Fecha_Vencimiento solo si están presentes
                if (isset($prod['Lote']) && $prod['Lote'] !== '') {
                    $oItem->Lote = strtoupper($prod['Lote']);
                }
                if (isset($prod['Fecha_Vencimiento']) && $prod['Fecha_Vencimiento'] !== '') {
                    $oItem->Fecha_Vencimiento = $this->validarFormatoFecha($prod['Fecha_Vencimiento']);
                }

                $oItem->save();
                unset($oItem);
            }

            // Iterar sobre los lotes del producto si existen
            if (isset($prod['Lotes']) && is_array($prod['Lotes'])) {
                foreach ($prod['Lotes'] as $item) {
                    if (isset($item['Cantidad_Encontrada']) && $item['Cantidad_Encontrada'] > 0) {
                        $oItem = new complex('Producto_Doc_Inventario_Fisico', 'Id_Producto_Doc_Inventario_Fisico');
                        $oItem->Id_Producto = $prod['Id_Producto'];
                        $oItem->Id_Inventario_Nuevo = !isset($item['Id_Inventario_Nuevo']) || $item['Id_Inventario_Nuevo'] == '' ? '0' : $item['Id_Inventario_Nuevo'];
                        $oItem->Primer_Conteo = $item['Cantidad_Encontrada'];
                        $oItem->Fecha_Primer_Conteo = date('Y-m-d');
                        $oItem->Cantidad_Inventario = !isset($item['Cantidad']) || $item['Cantidad'] == '' ? '0' : $item['Cantidad'];
                        $oItem->Id_Doc_Inventario_Fisico = $id_inventario_fisico;

                        // Asignar Lote y Fecha_Vencimiento solo si están presentes
                        if (isset($item['Lote']) && $item['Lote'] !== '') {
                            $oItem->Lote = strtoupper($item['Lote']);
                        }
                        if (isset($item['Fecha_Vencimiento']) && $item['Fecha_Vencimiento'] !== '') {
                            $oItem->Fecha_Vencimiento = $this->validarFormatoFecha($item['Fecha_Vencimiento']);
                        }

                        $oItem->save();
                        unset($oItem);
                    }
                }
            }
        }

        // Cambiar el estado del inventario fisico
        $oItem = new complex('Doc_Inventario_Fisico', 'Id_Doc_Inventario_Fisico', $id_inventario_fisico);
        $oItem->Estado = 'Primer Conteo';
        $band = $oItem->Id_Doc_Inventario_Fisico;
        $oItem->save();
        unset($oItem);

        if ($band) {
            $resultado['titulo'] = "Operación exitosa";
            $resultado['mensaje'] = "Se ha ajustado correctamente el inventario";
            $resultado['tipo'] = "success";
        } else {
            $resultado['titulo'] = "Error";
            $resultado['mensaje'] = "Ha ocurrido un error inesperado. Por favor verifica tu conexión a internet.";
            $resultado['tipo'] = "error";
        }

        return response()->json($resultado);
    }
    private function validarFormatoFecha($fecha)
    {
        $d = DateTime::createFromFormat('Y-m-d', $fecha);
        return $d && $d->format('Y-m-d') === $fecha ? $fecha : null;
    }

    function ValidarFecha($id_producto, $fecha)
    {
        $query = 'SELECT * FROM Producto  WHERE Id_Producto=' . $id_producto;
        $oCon = new consulta();
        $oCon->setQuery($query);
        $producto = $oCon->getData();
        unset($oCon);

        $fecha = '';
        /* if ($producto['Id_Categoria'] != 0) {
            if ($producto['Id_Categoria'] != 6 && $producto['Id_Categoria'] != 1) {
                if (isset($fecha)){
                    $fecha1 = explode(',', $fecha);
                    if ($fecha1[1] != 2) {
                        $fecha = $fecha1[0] . '-' . $fecha1[1] . '-30';
                    } else {
                        $fecha = $fecha1[0] . '-' . $fecha1[1] . '-28';
                    }
                }
            }
        } */
        return $fecha ?? '';
    }

    public function documentosAjustar(Request $request)
    {
        $idEstiba = $request->input('Id_Estiba', false);

        // Consulta para obtener Id_Grupo_Estiba y Id_Bodega_Nuevo
        $estiba = Estiba::select('Id_Grupo_Estiba', 'Id_Bodega_Nuevo')
            ->where('Id_Estiba', $idEstiba)
            ->first();

        if (!$estiba) {
            throw new Exception("Estiba not found");
        }

        $idGrupo = $estiba->Id_Grupo_Estiba;
        $idBodega = $estiba->Id_Bodega_Nuevo;

        // Consulta para obtener todas las estibas que pertenecen a un grupo
        $estibasPorGrupo = Estiba::select('Id_Estiba', 'Nombre as nombreEstiba')
            ->where('Estado', '!=', 'Inactiva')
            ->where('Id_Grupo_Estiba', $idGrupo)
            ->where('Id_Bodega_Nuevo', $idBodega)
            ->get();

        // Consulta para obtener todos los documentos que pertenecen a un grupo de la estiba que se encuentren en segundo conteo
        $documentos = DocInventarioFisico::select('Doc_Inventario_Fisico.*', 'Grupo_Estiba.Nombre as nombreGrupo', 'Estiba.Nombre as nombreEstiba')
            ->join('Estiba', 'Estiba.Id_Estiba', '=', 'Doc_Inventario_Fisico.Id_Estiba')
            ->join('Grupo_Estiba', 'Grupo_Estiba.Id_Grupo_Estiba', '=', 'Estiba.Id_Grupo_Estiba')
            ->where('Grupo_Estiba.Id_Grupo_Estiba', $idGrupo)
            ->where('Doc_Inventario_Fisico.Estado', 'Segundo Conteo')
            ->get();

        // Validar si faltan estibas
        $estibasFaltArray = [];
        foreach ($estibasPorGrupo as $estiba) {
            $idEstiba = $estiba->Id_Estiba;

            // Convertir documentos a arreglos si es necesario
            $documentosArray = $documentos->map(function ($doc) {
                return $doc->toArray();
            })->toArray();

            if (!in_array($idEstiba, array_column($documentosArray, 'Id_Estiba'))) {
                $estibasFaltArray[] = $idEstiba;
            }
            $estibasFalt = implode(', ', $estibasFaltArray);
        }

        if (strlen($estibasFalt) > 0) {
            $resultado = [
                'titulo' => 'Error',
                'mensaje' => "Las siguientes estibas pertenecen al mismo grupo y no se le han hecho el segundo conteo: $estibasFalt",
                'tipo' => 'error'
            ];
        } else {
            $productos = DocInventarioFisico::select(
                'Doc_Inventario_Fisico.Id_Doc_Inventario_Fisico',
                'Grupo_Estiba.Id_Grupo_Estiba',
                'Grupo_Estiba.Nombre as Nombre_Grupo',
                'Estiba.Id_Estiba',
                'Estiba.Nombre as Nombre_Estiba',
                'Bodega_Nuevo.Id_Bodega_Nuevo',
                'Bodega_Nuevo.Nombre as Nombre_Bodega',
                'Producto_Doc_Inventario_Fisico.*',
                'Producto.Nombre_General as Nombre_Producto',
                'Producto.Nombre_Comercial',
                DB::raw('(Producto_Doc_Inventario_Fisico.Segundo_Conteo - Producto_Doc_Inventario_Fisico.Cantidad_Inventario) as Cantidad_Diferencial')
            )
                ->join('Estiba', 'Estiba.Id_Estiba', '=', 'Doc_Inventario_Fisico.Id_Estiba')
                ->join('Grupo_Estiba', 'Grupo_Estiba.Id_Grupo_Estiba', '=', 'Estiba.Id_Grupo_Estiba')
                ->join('Bodega_Nuevo', 'Bodega_Nuevo.Id_Bodega_Nuevo', '=', 'Estiba.Id_Bodega_Nuevo')
                ->join('Producto_Doc_Inventario_Fisico', 'Producto_Doc_Inventario_Fisico.Id_Doc_Inventario_Fisico', '=', 'Doc_Inventario_Fisico.Id_Doc_Inventario_Fisico')
                ->join('Producto', 'Producto.Id_Producto', '=', 'Producto_Doc_Inventario_Fisico.Id_Producto')
                ->where('Grupo_Estiba.Id_Grupo_Estiba', $idGrupo)
                ->where('Doc_Inventario_Fisico.Estado', 'Segundo Conteo')
                ->orderBy('Estiba.Nombre')
                ->orderBy('Producto.Nombre_Comercial')
                ->get();

            $code = DocInventarioFisico::where('Id_Doc_Inventario_Fisico', $idGrupo)->first();
            $resultado = [
                'titulo' => 'Operación exitosa',
                'mensaje' => 'Los documentos se encuentran listos para ser ajustados',
                'data' => ['productos' => $productos],
                'code' => $documentos[0]->Codigo,
                'tipo' => 'success'
            ];
        }

        return response()->json($resultado);
    }

    public function iniciarInventarioBarrido()
    {
        $idCategoria = (isset($_REQUEST['idCategoria']) ? $_REQUEST['idCategoria'] : '');
        $idBodega = (isset($_REQUEST['idBodega']) ? $_REQUEST['idBodega'] : '');
        $Letras = (isset($_REQUEST['Letras']) ? $_REQUEST['Letras'] : '');
        $Contador = (isset($_REQUEST['Contador']) ? $_REQUEST['Contador'] : '');
        $Digitador = (isset($_REQUEST['Digitador']) ? $_REQUEST['Digitador'] : '');
        $Tipo = (isset($_REQUEST['Tipo']) ? $_REQUEST['Tipo'] : '');


        $inicio = date("Y-m-d H:i:s");

        $query = "SELECT Identificacion_Funcionario, Nombres, Apellidos,Imagen FROM Funcionario WHERE Identificacion_Funcionario=" . $Contador;
        $oCon = new consulta();
        $oCon->setQuery($query);
        $func_contador = $oCon->getData();
        unset($oCon);

        $query = "SELECT Identificacion_Funcionario, Nombres, Apellidos,Imagen FROM Funcionario WHERE Identificacion_Funcionario=" . $Digitador;
        $oCon = new consulta();
        $oCon->setQuery($query);
        $func_digitador = $oCon->getData();
        unset($oCon);


        if (isset($func_contador["Identificacion_Funcionario"]) && isset($func_digitador["Identificacion_Funcionario"])) {

            $oItem = new complex("Bodega", "Id_Bodega", $idBodega);
            $bodega = $oItem->getData();
            unset($oItem);
            if ($idCategoria != "Todas" && $idCategoria != 0) {
                $oItem = new complex("Categoria", "Id_Categoria", $idCategoria);
                $categoria = $oItem->getData();
                unset($oItem);
            } else {
                $categoria["Nombre"] = "Todas";
                $categoria["Id_Categoria"] = "0";
                $idCategoria = "0";
            }

            $oItem = new complex("Inventario_Fisico", "Id_Inventario_Fisico");
            $oItem->Fecha_Inicio = $inicio;
            $oItem->Bodega = $idBodega;
            $oItem->Categoria = $idCategoria;
            //$oItem->Letras = $Letras;
            $oItem->Conteo_Productos = 0;
            $oItem->Funcionario_Digita = $Digitador;
            $oItem->Funcionario_Cuenta = $Contador;
            $oItem->Tipo_Inventario = $Tipo;
            $oItem->save();
            $id_inv = $oItem->getId();
            unset($oItem);

            $resultado["Id_Inventario_Fisico"] = $id_inv;
            $resultado["Funcionario_Digita"] = $func_digitador;
            $resultado["Funcionario_Cuenta"] = $func_contador;
            $resultado["Bodega"] = $bodega;
            $resultado["Categoria"] = $categoria;
            $resultado["Letras"] = '';
            $resultado["Inicio"] = $inicio;
            $resultado["Productos_Conteo"] = 0;
            $resultado["Tipo_Inventario"] = $Tipo;
            $resultado["Tipo"] = "success";
            $resultado["Title"] = "Inventario iniciado correctamente";
            $resultado["Text"] = "Vamos a dar inicio al inventario físico por barrido de la siguiente categoria: \"" . $categoria['Nombre'] . "\".<br>¡Muchos Exitos!";
        } else {
            $resultado["Tipo"] = "error";
            $resultado["Title"] = "Error de funcionario";
            $resultado["Text"] = "Alguno de los documentos de los funcionarios no coinciden con funcionarios registrados en el sistema";
        }
        return response()->json($resultado);
    }

    public function consultaProductoBarrido()
    {
        $Categoria = (isset($_REQUEST['Categoria']) ? $_REQUEST['Categoria'] : '');
        $Bodega = (isset($_REQUEST['Bodega']) ? $_REQUEST['Bodega'] : '');
        $codigo = (isset($_REQUEST['Codigo']) ? $_REQUEST['Codigo'] : '');

        if ($Categoria == 'undefined') {
            $Categoria = 0;
        }
        $http_response = new HttpResponse();
        $queryObj = new QueryBaseDatos();
        $util = new Utility();

        $condicion = $this->SetCondiciones($codigo);
        $query = 'SELECT
            PRD.Id_Producto,
            PRD.Nombre_General as Nombre,
            PRD.Laboratorio_Comercial,
            PRD.Laboratorio_Generico,
            PRD.Unidad_Medida,
            IFNULL(PRD.Id_Categoria, 0) as Id_Categoria,
            IFNULL((SELECT Nombre FROM Categoria WHERE Id_Categoria = PRD.Id_Categoria), "Sin Categoria") as Categoria,
            PRD.Imagen,
            PRD.Codigo_Barras
        FROM Producto PRD
        LEFT JOIN Inventario I ON PRD.Id_Producto = I.Id_Producto ' . $condicion . '
        GROUP BY PRD.Id_Producto';

        $queryObj->SetQuery($query);
        $producto = $queryObj->ExecuteQuery('simple');

        if ($producto) {
            if ($producto['Id_Categoria'] != $Categoria && $Categoria != '0') {
                $resultado["Tipo"] = "error";
                $resultado["Titulo"] = "Categoría no coincide";
                $resultado["Texto"] = "La categoría del producto escaneado no coincide con la categoría inventariada<br><span class='font-weight-bold'>Categoría del producto escaneado: " . $producto['Categoria'] . "</span>";
            } else {
                $producto["Mensaje"] = "Producto encontrado exitosamente.";
                $resultado["Tipo"] = "success";
                $resultado["Datos"] = $producto;
            }
        } else {
            $resultado["Tipo"] = "error";
            $resultado["Titulo"] = "Producto no encontrado";
            $resultado["Texto"] = "El código de barras escaneado no coincide con ninguno de los productos que tenemos registrados.";
        }

        return response()->json($resultado);
    }



    public function agregaProductosBarrido()
    {
        $queryObj = new QueryBaseDatos();
        $response = array();
        $http_response = new HttpResponse();

        $id = (isset($_REQUEST['Id_Inventario_Fisico']) ? $_REQUEST['Id_Inventario_Fisico'] : '');
        $productos = (isset($_REQUEST['Productos']) ? $_REQUEST['Productos'] : '');

        $prod = (array) json_decode($productos, true);
        //var_dump($prod);


        $oItem = new complex("Inventario_Fisico", "Id_Inventario_Fisico", $id);
        $oItem->Lista_Productos = $productos;
        $oItem->save();
        unset($oItem);

        $http_response->SetRespuesta(0, 'Registro exitoso', '¡Producto agregado de manera exitosa!');
        $response = $http_response->GetRespuesta();

        return response()->json($response);
    }

    public function guardarInventarioFinal()
    {
        try {
            $contabilizar = new Contabilizar();
            $response = array();
            $http_response = new HttpResponse();

            // Obtener el cuerpo de la solicitud y decodificarlo desde JSON
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);

            // Asignar los valores de los campos del JSON
            $funcionario = auth()->user()->person_id;
            $inventarios = isset($data['inventarios']) ? $data['inventarios'] : false;
            $listado_inventario = isset($data['listado_inventario']) ? $data['listado_inventario'] : false;
            $ids_docs_inventarios = '';
            $ids_estibas = '';

            foreach ($listado_inventario as $res) {
                if (!strpos($ids_docs_inventarios, $res['Id_Doc_Inventario_Fisico'])) {
                    $ids_docs_inventarios .= ' ' . $res['Id_Doc_Inventario_Fisico'] . ' ,';
                }
                // filtrar los ids de las estibas para cambiarle el estado
                if (!strpos($ids_estibas, $res['Id_Estiba'])) {
                    $ids_estibas .= ' ' . $res['Id_Estiba'] . ' ,';
                }

                // Actualizar Producto_Doc_Inventario_Fisico con Cantidad_Auditada
                $cantidad_auditada = isset($res['Cantidad_Auditada']) && $res['Cantidad_Auditada'] != '' ? number_format($res['Cantidad_Auditada'], 0, "", "") : number_format($res['Segundo_Conteo'], 0, "", "");
                $this->ActualizarProductoDocumento($res['Id_Producto_Doc_Inventario_Fisico'], $cantidad_auditada, $funcionario);

                // Verificar existencia en Inventario_Nuevo considerando Lote y Fecha_Vencimiento
                $query = 'SELECT Id_Inventario_Nuevo FROM Inventario_Nuevo WHERE Id_Producto=' . $res["Id_Producto"] . ' AND Id_Estiba=' . $res['Id_Estiba'];
                if (isset($res['Lote']) && $res['Lote'] !== '') {
                    $query .= ' AND Lote="' . strtoupper($res['Lote']) . '"';
                }
                if (isset($res['Fecha_Vencimiento']) && $res['Fecha_Vencimiento'] !== '') {
                    $query .= ' AND Fecha_Vencimiento="' . $res['Fecha_Vencimiento'] . '"';
                }
                $query .= ' LIMIT 1';

                $oCon = new consulta();
                $oCon->setQuery($query);
                $inven = $oCon->getData();

                if ($inven) {
                    $oItem = new complex('Inventario_Nuevo', 'Id_Inventario_Nuevo', $inven['Id_Inventario_Nuevo']);
                    $oItem->Cantidad = $cantidad_auditada;
                    $oItem->Id_Estiba = $res['Id_Estiba'];
                    $oItem->Identificacion_Funcionario = $funcionario;
                    $oItem->Id_Punto_Dispensacion = 0;
                    $oItem->Cantidad_Apartada = '0';
                    $oItem->Cantidad_Seleccionada = '0';
                } else {
                    $oItem = new complex('Inventario_Nuevo', 'Id_Inventario_Nuevo');
                    $oItem->Cantidad = $cantidad_auditada;
                    $oItem->Id_Producto = $res["Id_Producto"];
                    $oItem->Id_Punto_Dispensacion = 0;
                    $oItem->Id_Estiba = $res['Id_Estiba'];
                    $oItem->Identificacion_Funcionario = $funcionario;
                    $oItem->Cantidad_Apartada = '0';
                    $oItem->Cantidad_Seleccionada = '0';
                    $oItem->Costo = $this->GetCosto($res["Id_Producto"]);
                    if (isset($res['Lote']) && $res['Lote'] !== '') {
                        $oItem->Lote = strtoupper($res['Lote']);
                    }
                    if (isset($res['Fecha_Vencimiento']) && $res['Fecha_Vencimiento'] !== '') {
                        $oItem->Fecha_Vencimiento = $this->validarFormatoFecha($res['Fecha_Vencimiento']);
                    }
                }
                $oItem->save();
                unset($oItem);
            }

            // Quitar la última coma (,) de la cadena de texto para que funcione la consulta
            $ids_docs_inventarios = substr($ids_docs_inventarios, 0, -1);
            $ids_estibas = substr($ids_estibas, 0, -1);
            $oItem = new complex('Inventario_Fisico_Nuevo', 'Id_Inventario_Fisico_Nuevo');
            $oItem->Funcionario_Autoriza = $funcionario;
            $oItem->Id_Bodega_Nuevo = $listado_inventario[0]['Id_Bodega_Nuevo'];
            $oItem->Id_Grupo_Estiba = $listado_inventario[0]['Id_Grupo_Estiba'];
            $oItem->Fecha = date('Y-m-d');
            $oItem->save();
            $inventario = $oItem->getId();
            unset($oItem);

            // Cambiar el estado del doc_invetario_fisico
            $query2 = 'UPDATE Doc_Inventario_Fisico
            SET Estado ="Terminado", Fecha_Fin="' . date('Y-m-d H:i:s') . '" , Funcionario_Autorizo=' . $funcionario . ',
                Id_Inventario_Fisico_Nuevo=' . $inventario . '
            WHERE  Id_Doc_Inventario_Fisico IN (' . $ids_docs_inventarios . ')';
            $oCon = new consulta();
            $oCon->setQuery($query2);
            $oCon->createData();
            unset($oCon);

            // Cambiar el estado de las estibas
            $query2 = 'UPDATE Estiba
            SET Estado = "Disponible"
            WHERE  Id_Estiba IN (' . $ids_estibas . ')';
            $oCon = new consulta();
            $oCon->setQuery($query2);
            $oCon->createData();
            unset($oCon);

            $resultado['titulo'] = "Registro exitoso";
            $resultado['mensaje'] = "¡Se ha guardado el inventario exitosamente!";
            $resultado['tipo'] = "success";
            return response()->json($resultado);
        } catch (Exception $e) {

            $resultado['titulo'] = "Error";
            $resultado['mensaje'] = $e->getMessage();
            $resultado['tipo'] = "error";
            return response()->json($resultado);
        }

    }


    function GetCosto($id_producto)
    {
        $query = 'SELECT IFNULL((SELECT Precio FROM Producto_Acta_Recepcion WHERE Id_Producto=' . $id_producto . ' Order BY Id_Producto_Acta_Recepcion DESC LIMIT 1 ), 0) as Costo  ';

        $oCon = new consulta();
        $oCon->setQuery($query);
        $costo = $oCon->getData();
        unset($oCon);

        return $costo['Costo'];
    }

    function AsignarIdInventarioFisico($inventarios)
    {
        $inv = explode(',', $inventarios);

        return $inv[0];
    }


    function ActualizarProductoDocumento($id_producto_doc_inventario, $cantidad, $funcionario)
    {

        // actualizar el documento con la cantidad ingresada por el auditor
        $query = 'UPDATE Producto_Doc_Inventario_Fisico SET Cantidad_Auditada =' . $cantidad . ' , Funcionario_Cantidad_Auditada = ' . $funcionario . '
        WHERE Id_Producto_Doc_Inventario_Fisico = ' . $id_producto_doc_inventario;
        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->createData();
    }
    public function validarInventario(Request $request)
    {
        // Obtener el ID del inventario físico desde la solicitud
        $id_doc_inventario_fisico = $request->input('inv', '');

        // Consultar el inventario físico por su ID
        $inventario = DocInventarioFisico::find($id_doc_inventario_fisico);


        // Verificar si el inventario existe y tiene un ID de estiba definido
        if (!$inventario || $inventario->Id_Estiba === null) {
            throw new Exception("Id_Estiba no está definido o es null.");
        }

        // Verificar el estado del inventario
        if (strtolower($inventario->Estado) == strtolower('Primer Conteo') || strtolower($inventario->Estado) == strtolower('Haciendo Segundo Conteo')) {
            // Obtener los productos relacionados al inventario físico
            $producto_inventario_fisico = $this->GetProductos($inventario->Id_Doc_Inventario_Fisico, $inventario->Id_Estiba);
            $producto_no_contados = $this->GetProductosNoContados($inventario->Id_Doc_Inventario_Fisico, $inventario->Id_Estiba);

            // Generar una lista de IDs de productos en el inventario físico
            $ids_prod_inv_fisico = implode(', ', $producto_inventario_fisico->pluck('Id_Producto_Doc_Inventario_Fisico')->toArray());


            // Obtener productos no inventariados
            $producto_no_inventario = $this->GetProductosNoInventario($inventario->Id_Doc_Inventario_Fisico, $ids_prod_inv_fisico);

            // Combinar todas las listas de productos
            $lista = $producto_inventario_fisico->merge($producto_no_contados)->merge($producto_no_inventario);

            // Separar la lista en productos con y sin diferencia
            $listaSinDiferencia = $lista->filter(function ($producto) {
                return $producto->Cantidad_Diferencial == '0';
            });

            $listaConDiferencia = $lista->filter(function ($producto) {
                return $producto->Cantidad_Diferencial != '0';
            });

            // Ordenar las listas por nombre comercial
            $listaSinDiferencia = $listaSinDiferencia->sortBy('Nombre_Comercial');
            $listaConDiferencia = $listaConDiferencia->sortBy('Nombre_Comercial');

            // Combinar las listas ordenadas
            $lista = $listaConDiferencia->merge($listaSinDiferencia);

            // Calcular la Cantidad_Final para cada producto
            $lista->each(function ($producto) {
                $producto->Cantidad_Final = $producto->Cantidad_Inventario - $producto->Cantidad_Encontrada;
            });

            $resultado = [
                'tipo' => "success",
                'Productos' => $listaConDiferencia,
                'Productos_Sin_Diferencia' => $listaSinDiferencia,
                'Estado' => $inventario->Estado,
                'Inventarios' => $inventario->Id_Doc_Inventario_Fisico,
                'Codigo' => $inventario->Codigo,
            ];
        } else {
            $resultado = [
                'tipo' => "error",
                'titulo' => "No puede realizar esta acción",
                'mensaje' => "Para este inventario ya se está realizando un reconteo, por favor verifica",
            ];
        }

        // Retornar la respuesta en formato JSON
        return response()->json($resultado);
    }

    // Obtener productos del inventario físico
    private function GetProductos($idInventarioFisico, $idEstiba)
    {
        $sql = '
    GROUP_CONCAT(Producto_Doc_Inventario_Fisico.Id_Producto_Doc_Inventario_Fisico) AS Id_Producto_Doc_Inventario_Fisico,
    Producto_Doc_Inventario_Fisico.Id_Doc_Inventario_Fisico, Producto_Doc_Inventario_Fisico.Id_Inventario_Nuevo,
    SUM(Producto_Doc_Inventario_Fisico.Primer_Conteo) AS Cantidad_Encontrada,
    CASE
        WHEN SUM(Producto_Doc_Inventario_Fisico.Primer_Conteo) = (SELECT SUM(Inventario_Nuevo.Cantidad) FROM Inventario_Nuevo WHERE Inventario_Nuevo.Id_Producto = Producto_Doc_Inventario_Fisico.Id_Producto AND Inventario_Nuevo.Id_Estiba = ?) THEN 0
        WHEN SUM(Producto_Doc_Inventario_Fisico.Primer_Conteo) < (SELECT SUM(Inventario_Nuevo.Cantidad) FROM Inventario_Nuevo WHERE Inventario_Nuevo.Id_Producto = Producto_Doc_Inventario_Fisico.Id_Producto AND Inventario_Nuevo.Id_Estiba = ?) THEN CONCAT("", (SUM(Producto_Doc_Inventario_Fisico.Primer_Conteo) - (SELECT SUM(Inventario_Nuevo.Cantidad) FROM Inventario_Nuevo WHERE Inventario_Nuevo.Id_Producto = Producto_Doc_Inventario_Fisico.Id_Producto AND Inventario_Nuevo.Id_Estiba = ?)))
        WHEN SUM(Producto_Doc_Inventario_Fisico.Primer_Conteo) > (SELECT SUM(Inventario_Nuevo.Cantidad) FROM Inventario_Nuevo WHERE Inventario_Nuevo.Id_Producto = Producto_Doc_Inventario_Fisico.Id_Producto AND Inventario_Nuevo.Id_Estiba = ?) THEN CONCAT("+", (SUM(Producto_Doc_Inventario_Fisico.Primer_Conteo) - (SELECT SUM(Inventario_Nuevo.Cantidad) FROM Inventario_Nuevo WHERE Inventario_Nuevo.Id_Producto = Producto_Doc_Inventario_Fisico.Id_Producto AND Inventario_Nuevo.Id_Estiba = ?)))
    END AS Cantidad_Diferencial,
    (SELECT SUM(Inventario_Nuevo.Cantidad) FROM Inventario_Nuevo WHERE Inventario_Nuevo.Id_Producto = Producto_Doc_Inventario_Fisico.Id_Producto AND Inventario_Nuevo.Id_Estiba = ?) AS Cantidad_Inventario,
    "" AS Cantidad_Final,
    Producto_Doc_Inventario_Fisico.Id_Producto,
    Producto.Nombre_Comercial,
    Producto.Nombre_General AS Nombre_Producto
';

        return ProductoDocInventarioFisico::selectRaw($sql, [
            $idEstiba,
            $idEstiba,
            $idEstiba,
            $idEstiba,
            $idEstiba,
            $idEstiba
        ])
            ->join('Producto', 'Producto_Doc_Inventario_Fisico.Id_Producto', '=', 'Producto.Id_Producto')
            ->whereIn('Producto_Doc_Inventario_Fisico.Id_Doc_Inventario_Fisico', [$idInventarioFisico])
            ->groupBy('Producto_Doc_Inventario_Fisico.Id_Producto')
            ->havingRaw('Cantidad_Inventario IS NOT NULL')
            ->orderBy('Producto.Nombre_Comercial')
            ->get();
    }

    private function GetProductosNoContados($idInventarioFisico, $idEstiba)
    {
        $query_inventario = ProductoDocInventarioFisico::select('Id_Inventario_Nuevo')
            ->whereIn('Producto_Doc_Inventario_Fisico.Id_Doc_Inventario_Fisico', [$idInventarioFisico])
            ->toSql();

        $bindings = ProductoDocInventarioFisico::select('Id_Inventario_Nuevo')
            ->whereIn('Producto_Doc_Inventario_Fisico.Id_Doc_Inventario_Fisico', [$idInventarioFisico])
            ->getBindings();

        return InventarioNuevo::selectRaw('
    CONCAT("-", Inventario_Nuevo.Cantidad) as Cantidad_Diferencial,
    0 as Cantidad_Encontrada,
    Inventario_Nuevo.Id_Producto,
    0 as Id_Producto_Doc_Inventario_Fisico,
    Producto.Nombre_Comercial,
    Producto.Nombre_General AS Nombre_Producto,
    Inventario_Nuevo.Cantidad as Segundo_Conteo,
    Inventario_Nuevo.Cantidad as Cantidad_Inventario,
    Inventario_Nuevo.Id_Inventario_Nuevo
')
            ->join('Producto', 'Inventario_Nuevo.Id_Producto', '=', 'Producto.Id_Producto')
            ->whereRaw('Inventario_Nuevo.Id_Inventario_Nuevo NOT IN (' . $query_inventario . ')', $bindings)
            ->where('Inventario_Nuevo.Id_Estiba', $idEstiba)
            ->where('Inventario_Nuevo.Cantidad', '>', 0)
            ->orderBy('Producto.Nombre_Comercial')
            ->get();
    }

    // Obtener productos no contados
    private function GetProductosNoInventario($idInventarioFisico, $idProductos)
    {
        return ProductoDocInventarioFisico::selectRaw('
    CONCAT("+", Producto_Doc_Inventario_Fisico.Primer_Conteo) as Cantidad_Diferencial,
    Producto_Doc_Inventario_Fisico.Primer_Conteo as Cantidad_Encontrada,
    Producto_Doc_Inventario_Fisico.Id_Producto,
    Producto_Doc_Inventario_Fisico.Id_Producto_Doc_Inventario_Fisico,
    Producto.Nombre_Comercial,
    Producto.Nombre_General AS Nombre_Producto,
    0 as Segundo_Conteo,
    0 as Cantidad_Inventario,
    0 AS Id_Inventario_Nuevo
')
            ->join('Producto', 'Producto_Doc_Inventario_Fisico.Id_Producto', '=', 'Producto.Id_Producto')
            ->whereNotIn('Producto_Doc_Inventario_Fisico.Id_Producto_Doc_Inventario_Fisico', explode(',', $idProductos))
            ->whereIn('Producto_Doc_Inventario_Fisico.Id_Doc_Inventario_Fisico', [$idInventarioFisico])
            ->orderBy('Producto.Nombre_Comercial')
            ->get();
    }

    //ordena la lista muntidimencional por nombre
    function ordenarListaNombre($lista, $campo)
    {
        $position = array();
        $newRow = array();
        foreach ($lista as $key => $row) {
            $position[$key] = $row->$campo;
            $newRow[$key] = $row;
        }
        asort($position);
        $returnArray = array();
        foreach ($position as $key => $pos) {
            $returnArray[] = $newRow[$key];
        }
        return $returnArray;
    }

    public function inventarioSinDiferenciaBarrido()
    {
        $id_inventario_fisico = (isset($_REQUEST['inv']) ? $_REQUEST['inv'] : '');

        // Verificar si los campos Lote y Fecha_Vencimiento existen en la tabla
        $columnCheckQuery = "SHOW COLUMNS FROM Producto_Inventario_Fisico LIKE 'Lote'";
        $oCon = new consulta();
        $oCon->setTipo('Multiple');
        $oCon->setQuery($columnCheckQuery);
        $loteExists = count($oCon->getData()) > 0;

        $columnCheckQuery = "SHOW COLUMNS FROM Producto_Inventario_Fisico LIKE 'Fecha_Vencimiento'";
        $oCon->setQuery($columnCheckQuery);
        $fechaVencimientoExists = count($oCon->getData()) > 0;
        unset($oCon);

        // Construir la consulta SQL dinámica
        $selectFields = [
            "PIF.Id_Producto_Inventario_Fisico as Id_Producto_Inventario_Fisico",
            "PIF.Id_Inventario_Fisico",
            "PIF.Id_Inventario",
            "P.Nombre_Comercial",
            "P.Nombre_General AS Nombre_Producto",
            "SUM(PIF.Primer_Conteo) AS Cantidad_Encontrada",
            "CASE
            WHEN SUM(PIF.Primer_Conteo) < SUM(PIF.Cantidad_Inventario) THEN CONCAT('<', (SUM(PIF.Cantidad_Inventario) - SUM(PIF.Primer_Conteo)))
            WHEN SUM(PIF.Primer_Conteo) > SUM(PIF.Cantidad_Inventario) THEN CONCAT('>', (SUM(PIF.Primer_Conteo) - SUM(PIF.Cantidad_Inventario)))
        END AS Cantidad_Diferencial",
            "SUM(PIF.Cantidad_Inventario) as Cantidad",
            "PIF.Cantidad_Inventario",
            "'' AS Cantidad_Final"
        ];

        if ($loteExists) {
            $selectFields[] = "PIF.Lote";
        }

        if ($fechaVencimientoExists) {
            $selectFields[] = "PIF.Fecha_Vencimiento";
        }

        $query = "SELECT " . implode(", ", $selectFields) . "
        FROM Producto_Inventario_Fisico PIF
        INNER JOIN Producto P ON PIF.Id_Producto = P.Id_Producto
        WHERE PIF.Id_Inventario_Fisico IN (" . $id_inventario_fisico . ")
        GROUP BY PIF.Id_Producto" . ($loteExists ? ", PIF.Lote" : "") . "
        HAVING Cantidad_Encontrada = Cantidad
        ORDER BY P.Nombre_Comercial";

        $oCon = new consulta();
        $oCon->setTipo('Multiple');
        $oCon->setQuery($query);
        $resultado = $oCon->getData();
        unset($oCon);

        foreach ($resultado as $i => $res) {
            $resultado[$i]['Cantidad_Encontrada'] = (int) $res['Cantidad_Encontrada'];
            $resultado[$i]['Cantidad_Inventario'] = (int) $res['Cantidad_Inventario'];
        }

        return response()->json($resultado);
    }



    public function guardarReconteo()
    {
        $response = array();

        // Obtener el contenido de la solicitud HTTP
        $requestBody = file_get_contents('php://input');
        $data = json_decode($requestBody, true);

        // Verificar si la decodificación de JSON fue exitosa
        if (json_last_error() === JSON_ERROR_NONE) {
            // Asignar los datos de 'listado_inventario' e 'inventarios' si existen
            $listado_inventario = $data['listado_inventario'] ?? [];
            $inventarios = $data['inventarios'] ?? false;
        } else {
            // Devolver una respuesta de error si el JSON no es válido
            return response()->json(['error' => 'Invalid JSON data'], 400);
        }

        // Obtener el person_id del usuario autenticado
        $funcionario = Auth::user()->person_id;

        // Iterar sobre cada elemento en 'listado_inventario'
        foreach ($listado_inventario as $value) {
            if ($value['Id_Producto_Doc_Inventario_Fisico'] != 0) {
                // Si 'Id_Producto_Doc_Inventario_Fisico' no es 0, explotar el valor por comas y procesar cada ID
                $id_inventario = explode(",", $value['Id_Producto_Doc_Inventario_Fisico']);
                foreach ($id_inventario as $i => $id) {
                    // Buscar el registro en la base de datos usando Eloquent
                    $productoInventario = ProductoDocInventarioFisico::find($id);
                    if ($i == 0) {
                        // Si es el primer elemento, actualizar los campos correspondientes
                        $productoInventario->Segundo_Conteo = number_format((int) $value['Cantidad_Final'], 0, '', '');
                        $productoInventario->Primer_Conteo = number_format((int) $value['Cantidad_Encontrada'], 0, '', '');
                        $productoInventario->Cantidad_Inventario = $value['Cantidad_Inventario'];
                        $productoInventario->Fecha_Segundo_Conteo = now();

                        // Verificar y asignar Lote y Fecha_Vencimiento si existen
                        if (isset($value['Lote'])) {
                            $productoInventario->Lote = strtoupper($value['Lote']);
                        }
                        if (isset($value['Fecha_Vencimiento'])) {
                            $productoInventario->Fecha_Vencimiento = $value['Fecha_Vencimiento'];
                        }

                        $productoInventario->save(); // Guardar los cambios en la base de datos
                    } else {
                        // Si no es el primer elemento, eliminar el registro
                        $productoInventario->delete();
                    }
                }
            } else {
                // Si 'Id_Producto_Doc_Inventario_Fisico' es 0, crear un nuevo registro
                $productoInventario = new ProductoDocInventarioFisico();
                $productoInventario->Segundo_Conteo = number_format((int) $value['Cantidad_Final'], 0, '', '');
                $productoInventario->Id_Producto = $value['Id_Producto'];
                $productoInventario->Id_Inventario_Nuevo = $value['Id_Inventario_Nuevo'];
                $productoInventario->Primer_Conteo = "0";
                $productoInventario->Fecha_Primer_Conteo = now();
                $productoInventario->Fecha_Segundo_Conteo = now();
                $productoInventario->Cantidad_Inventario = number_format($value['Cantidad_Inventario'], 0, "", "");
                $productoInventario->Id_Doc_Inventario_Fisico = $this->AsignarIdInventarioFisico($inventarios);

                // Verificar y asignar Lote y Fecha_Vencimiento si existen
                if (isset($value['Lote'])) {
                    $productoInventario->Lote = strtoupper($value['Lote']);
                }
                if (isset($value['Fecha_Vencimiento'])) {
                    $productoInventario->Fecha_Vencimiento = $value['Fecha_Vencimiento'];
                }

                $productoInventario->save(); // Guardar el nuevo registro en la base de datos
            }
        }

        // Actualizar el estado y la fecha de finalización en la tabla 'Doc_Inventario_Fisico'
        DocInventarioFisico::whereIn('Id_Doc_Inventario_Fisico', explode(',', $inventarios))
            ->update([
                'Estado' => 'Segundo Conteo',
                'Fecha_Fin' => now(),
                'Funcionario_Autorizo' => $funcionario
            ]);

        // Actualizar los registros en 'Producto_Doc_Inventario_Fisico' donde 'Segundo_Conteo' es nulo
        ProductoDocInventarioFisico::whereIn('Id_Doc_Inventario_Fisico', explode(',', $inventarios))
            ->whereNull('Segundo_Conteo')
            ->update([
                'Segundo_Conteo' => DB::raw('Primer_Conteo')
            ]);

        // Preparar y devolver la respuesta de éxito
        $resultado['titulo'] = "Operación exitosa";
        $resultado['mensaje'] = "¡Se ha guardado el segundo conteo exitosamente!";
        $resultado['tipo'] = "success";
        return response()->json($resultado);
    }



    public function excelDiferencias()
    {
        $productos = request('productos') ?: [];
        $inv = request('id_doc_inventario');

        foreach ($productos as $key => $p) {
            $productos[$key]['Costo'] = $this->GetCostoPromedio($p['Id_Producto']);
            $productos[$key]['Valor_Inicial'] = $productos[$key]['Cantidad_Inventario'] * $productos[$key]['Costo'];
            $productos[$key]['Valor_Final'] = $productos[$key]['Cantidad_Final'] * $productos[$key]['Costo'];
        }

        return Excel::download(new ProductosExport($productos), 'diferencias.xlsx');
    }

    function GetCostoPromedio($id)
    {

        $query = "SELECT ROUND(AVG(Costo_Promedio)) as Costo FROM Costo_Promedio WHERE Id_Producto=$id";

        $oCon = new consulta();
        $oCon->setQuery($query);
        $costo = $oCon->getData();
        unset($oCon);


        return $costo['Costo'] ? $costo['Costo'] : 0;
    }


    function fecha($str)
    {
        $parts = explode(" ", $str);
        $date = explode("-", $parts[0]);
        return $date[2] . "/" . $date[1] . "/" . $date[0];
    }
    function getLabComercial($id_prod)
    {
        $query = "SELECT Laboratorio_Comercial FROM Producto WHERE Id_Producto=$id_prod  ";
        $oCon = new consulta();
        $oCon->setQuery($query);
        $data = $oCon->getData();
        unset($oCon);

        return $data['Laboratorio_Comercial'];
    }

    public function verInventarioTerminado(Request $request)
    {
        // Obtener el ID de Inventario Fisico Nuevo desde la solicitud
        $id_inventario_fisico_nuevo = $request->input('Id_Inventario_Fisico_Nuevo');

        // Verificar si el ID está presente, si no, retornar un error
        if (!$id_inventario_fisico_nuevo) {
            return response()->json([
                'Tipo' => 'error',
                'Titulo' => 'Error al intentar buscar las bodegas',
                'Texto' => 'El ID de inventario fisico es requerido.',
            ]);
        }

        // Realizar la consulta utilizando Eloquent, incluyendo las relaciones necesarias
        $inventario = InventarioFisicoNuevo::with([
            'docInventarioFisico.estiba.grupoEstiba',
            'docInventarioFisico.productoDocInventarioFisico.producto'
        ])
            ->where('Id_Inventario_Fisico_Nuevo', $id_inventario_fisico_nuevo)
            ->get()
            ->map(function ($inventarioFisico) {
                // Mapear los resultados de InventarioFisicoNuevo
                return $inventarioFisico->docInventarioFisico->flatMap(function ($docInventarioFisico) {
                    // Mapear los resultados de DocInventarioFisico
                    return $docInventarioFisico->productoDocInventarioFisico->map(function ($productoDocInventarioFisico) use ($docInventarioFisico) {
                        // Mapear los resultados de ProductoDocInventarioFisico
                        return [
                            'Fecha_Realizado' => $docInventarioFisico->inventarioFisicoNuevo->Fecha, // Fecha del Inventario Fisico Nuevo
                            'Nombre_Estiba' => $docInventarioFisico->estiba->Nombre, // Nombre de la Estiba
                            'Nombre_Grupo' => $docInventarioFisico->estiba->grupoEstiba->Nombre, // Nombre del Grupo de Estiba
                            'Id_Producto_Doc_Inventario_Fisico' => $productoDocInventarioFisico->Id_Producto_Doc_Inventario_Fisico,
                            'Id_Producto' => $productoDocInventarioFisico->Id_Producto,
                            'Id_Inventario_Nuevo' => $productoDocInventarioFisico->Id_Inventario_Nuevo,
                            'Primer_Conteo' => $productoDocInventarioFisico->Primer_Conteo,
                            'Fecha_Primer_Conteo' => $productoDocInventarioFisico->Fecha_Primer_Conteo,
                            'Segundo_Conteo' => $productoDocInventarioFisico->Segundo_Conteo,
                            'Fecha_Segundo_Conteo' => $productoDocInventarioFisico->Fecha_Segundo_Conteo,
                            'Cantidad_Auditada' => $productoDocInventarioFisico->Cantidad_Auditada,
                            'Funcionario_Cantidad_Auditada' => $productoDocInventarioFisico->Funcionario_Cantidad_Auditada,
                            'Cantidad_Inventario' => $productoDocInventarioFisico->Cantidad_Inventario,
                            'Id_Doc_Inventario_Fisico' => $productoDocInventarioFisico->Id_Doc_Inventario_Fisico,
                            'Actualizado' => $productoDocInventarioFisico->Actualizado,
                            'Cantidad_Diferencial' => $productoDocInventarioFisico->Segundo_Conteo - $productoDocInventarioFisico->Cantidad_Inventario, // Diferencial de Cantidad
                            'Nombre_Producto' => $productoDocInventarioFisico->producto->Nombre_General, // Nombre General del Producto
                            'Nombre_Comercial' => $productoDocInventarioFisico->producto->Nombre_Comercial, // Nombre Comercial del Producto
                            'Codigo' => $docInventarioFisico->Codigo,
                        ];
                    });
                });
            })->flatten(1); // Aplanar el resultado a un nivel

        // Verificar si se encontraron inventarios
        if ($inventario->isNotEmpty()) {
            // Retornar respuesta de éxito con los datos del inventario
            return response()->json([
                'Tipo' => 'success',
                'Inventario' => $inventario
            ]);
        } else {
            // Retornar respuesta de error si no se encontraron inventarios
            return response()->json([
                'Tipo' => 'error',
                'Titulo' => 'Error al intentar buscar las bodegas',
                'Texto' => 'Ha ocurrido un error inesperado.'
            ]);
        }
    }




    public function cambiarEstadosDocumentos()
    {
        $estado = isset($_REQUEST['estado']) ? $_REQUEST['estado'] : '';
        $idDocumento = isset($_REQUEST['idDocumento']) ? $_REQUEST['idDocumento'] : '';
        $Tipo = isset($_REQUEST['Tipo']) ? $_REQUEST['Tipo'] : '';

        if ($estado && $idDocumento) {

            if ($Tipo != 'Auditoria') {
                $oItem = new complex('Doc_Inventario_Fisico', 'Id_Doc_Inventario_Fisico', $idDocumento);
                $oItem->getData();
                $oItem->Estado = $estado;
                $oItem->save();
                $response['tipo'] = 'success';
                $response['title'] = 'Cambio de estado exitoso';
                $response['mensaje'] = 'Documento actualizado con éxito';
                return response()->json($response);
            }

            $oItem = new complex('Doc_Inventario_Auditable', 'Id_Doc_Inventario_Auditable', $idDocumento);
            $oItem->getData();
            $oItem->Estado = $estado;
            $oItem->save();
            $response['tipo'] = 'success';
            $response['title'] = 'Cambio de estado exitoso';
            $response['mensaje'] = 'Documento actualizado con éxito';
            return response()->json($response);
        }
    }

    public function getGrupoEstibas()
    {
        $query = 'Select * From Grupo_Estiba';
        $oCon = new consulta();
        $oCon->setTipo('Multiple');
        $oCon->setQuery($query);
        $Grupo_Estibas = $oCon->getData();
        unset($oCon);

        if ($Grupo_Estibas) {

            $producto["Mensaje"] = 'Estibas Encontradas con éxito';
            $resultado["Tipo"] = "success";
            $resultado["Grupo_Estibas"] = $Grupo_Estibas;
        } else {
            $resultado["Tipo"] = "error";
            $resultado["Titulo"] = "Error al intentar buscar las bodegas";
            $resultado["Texto"] = "Ha ocurrido un error inesperado.";
        }
        return response()->json($resultado);
    }

    public function documentosTerminados(Request $request)
    {

        $bodegaId = $request->query('bodega', null);
        $grupoId = $request->query('grupo', null);
        $pageSize = $request->query('pageSize', 100);
        $page = $request->query('page', 1);

        // Construcción de la consulta para Inventario General
        $queryGeneral = InventarioFisicoNuevo::query()
            ->select([
                'Id_Inventario_Fisico_Nuevo',
                'fecha',
                'Bodega_Nuevo.nombre as Nombre_Bodega',
                'Grupo_Estiba.nombre as Nombre_Grupo',
                'people.first_name as Nombre_Funcionario_Autorizo',
                DB::raw('"General" as Tipo')
            ])
            ->join('Grupo_Estiba', 'Grupo_Estiba.Id_Grupo_Estiba', '=', 'Inventario_Fisico_Nuevo.Id_Grupo_Estiba')
            ->join('Bodega_Nuevo', 'Bodega_Nuevo.Id_Bodega_Nuevo', '=', 'Inventario_Fisico_Nuevo.Id_Bodega_Nuevo')
            ->join('people', 'people.identifier', '=', 'Inventario_Fisico_Nuevo.Funcionario_Autoriza');

        $queryAuditoria = DocInventarioAuditable::query()
            ->select([
                'Id_Doc_Inventario_Auditable',
                'fecha_fin as Fecha',
                'Bodega_Nuevo.nombre as Nombre_Bodega',
                DB::raw('"Sin Grupo" as Nombre_Grupo'),
                'people.first_name as Nombre_Funcionario_Autorizo',
                DB::raw('"Auditoria" as Tipo')
            ])
            ->join('Bodega_Nuevo', 'Bodega_Nuevo.Id_Bodega_Nuevo', '=', 'Doc_Inventario_Auditable.Id_Bodega')
            ->join('people', 'people.identifier', '=', 'Doc_Inventario_Auditable.Funcionario_Autorizo');


        $unionQuery = $queryGeneral->unionAll($queryAuditoria)->paginate(request()->get('tam', 10), ['*'], 'page', request()->get('pag', 1));

        return $this->success($unionQuery);
    }


    function SetCondicionesTerminadas($req)
    {
        global $util;

        $condicion = '';

        if (isset($req['grupo']) && $req['grupo']) {
            $condicion .= " WHERE Datas.Id_Grupo_Estiba='$req[grupo]'";
        }

        if (isset($req['bodega']) && $req['bodega']) {
            if ($condicion != "") {
                $condicion .= " AND Datas.Id_Bodega_Nuevo='$req[bodega]'";
            } else {
                $condicion .= " WHERE Datas.Id_Bodega_Nuevo='$req[bodega]'";
            }
        }

        if (isset($req['fechas']) && $req['fechas']) {
            $fechas_separadas = $util->SepararFechas($req['fechas']);
            if ($condicion != "") {
                $condicion .= " AND DATE(Datas.Fecha) BETWEEN '$fechas_separadas[0]' AND '$fechas_separadas[1]'";
            } else {
                $condicion .= " WHERE DATE(Datas.Fecha) BETWEEN '$fechas_separadas[0]' AND '$fechas_separadas[1]'";
            }
        }
        return $condicion;
    }

    public function filterInventario(Request $request)
    {
        $query = InventarioNuevo::query()
            ->selectRaw('
            GROUP_CONCAT(I.Id_Inventario_Nuevo) AS Id_Inventario_Nuevo,
            I.Cantidad,
            GE.Nombre AS Nombre_Grupo,
            I.Id_Producto,
            B.Id_Punto_Dispensacion,
            SUM(I.Cantidad_Apartada) AS Cantidad_Apartada,
            SUM(I.Cantidad_Seleccionada) AS Cantidad_Seleccionada,
            PRD.Nombre_Comercial,
            PRD.Nombre_General,
            PRD.Referencia,
            PRD.Unidad_Empaque,
            (SELECT CPM.Costo_Promedio FROM Costo_Promedio CPM WHERE CPM.Id_Producto = PRD.Id_Producto) AS Costo,
            PRD.Unidad_Empaque AS Embalaje,
            SUM(
                IF(
                    (
                        I.Cantidad - I.Cantidad_Apartada - I.Cantidad_Seleccionada
                    ) < 0,
                    0,
                    (
                        I.Cantidad - I.Cantidad_Apartada - I.Cantidad_Seleccionada
                    )
                )
            ) AS Cantidad_Disponible,
            C.Nombre AS Nombre_Categoria,
            GROUP_CONCAT(CONCAT ( " Estiba ", E.Nombre , "  : ",
                                    IF(
                                        (
                                            I.Cantidad - I.Cantidad_Apartada - I.Cantidad_Seleccionada
                                        ) < 0,
                                        0,
                                        (
                                            I.Cantidad - I.Cantidad_Apartada - I.Cantidad_Seleccionada
                                        )
                                    )
                                )
                        ) AS "Nombre_Estiba",
            PRD.Gravado
        ')
            ->from('Inventario_Nuevo as I')
            ->join('Producto as PRD', 'I.Id_Producto', '=', 'PRD.Id_Producto')
            ->join('Estiba as E', 'E.Id_Estiba', '=', 'I.Id_Estiba')
            ->join('Grupo_Estiba as GE', 'GE.Id_Grupo_Estiba', '=', 'E.Id_Grupo_Estiba')
            ->join('Punto_Dispensacion as B', 'B.Id_Punto_Dispensacion', '=', 'E.Id_Punto_Dispensacion')
            ->join('Subcategoria as SubC', 'PRD.Id_Subcategoria', '=', 'SubC.Id_Subcategoria')
            ->join('Categoria_Nueva as C', 'SubC.Id_Categoria_Nueva', '=', 'C.Id_Categoria_Nueva')
            ->groupBy('B.Id_Punto_Dispensacion', 'I.Id_Producto')
            ->orderBy('PRD.Nombre_General');

        if ($request->has('sin_inventario') && $request->input('sin_inventario') === "false") {
            $query->whereRaw('(I.Cantidad - I.Cantidad_Apartada - I.Cantidad_Seleccionada) > 0');
        }

        if ($request->filled('nom')) {
            $query->where(function ($subQuery) use ($request) {
                $subQuery->where('PRD.Principio_Activo', 'like', '%' . $request->input('nom') . '%')
                    ->orWhere('PRD.Presentacion', 'like', '%' . $request->input('nom') . '%')
                    ->orWhere('PRD.Concentracion', 'like', '%' . $request->input('nom') . '%')
                    ->orWhere('PRD.Nombre_General', 'like', '%' . $request->input('nom') . '%');
            });
        }

        if ($request->filled('grupo')) {
            $query->where('GE.Nombre', 'like', '%' . $request->input('grupo') . '%');
        }

        if ($request->filled('bod')) {
            $query->where('B.Nombre', 'like', '%' . $request->input('bod') . '%');
        }

        if ($request->filled('cant')) {
            $query->whereRaw('(I.Cantidad - I.Cantidad_Apartada - I.Cantidad_Seleccionada) = ?', [$request->input('cant')]);
        }

        if ($request->filled('cant_apar')) {
            $query->where('I.Cantidad_Apartada', $request->input('cant_apar'));
        }

        if ($request->filled('cant_sel')) {
            $query->where('I.Cantidad_Seleccionada', $request->input('cant_sel'));
        }

        if ($request->filled('costo')) {
            $query->where('I.Costo', $request->input('costo'));
        }

        if ($request->filled('invima')) {
            $query->where('PRD.Invima', 'like', '%' . $request->input('invima') . '%');
        }

        if ($request->filled('iva')) {
            $query->where('PRD.Gravado', $request->input('iva'));
        }

        if ($request->filled('id') && $request->input('id') != 0) {
            $query->where('B.Id_Punto_Dispensacion', $request->input('id'));
        } else {
            $query->where('B.Id_Punto_Dispensacion', '!=', 0);
        }

        $inventario = $query->paginate(
            $request->input('pageSize', 10),
            ['*'],
            'page',
            $request->input('page', 1)
        );

        return response()->json(['success' => true, 'data' => $inventario], 200);
    }

    public function getGrupoBodega()
    {
        $idBodega = isset($_REQUEST['id_bodega_nuevo']) ? $_REQUEST['id_bodega_nuevo'] : '';

        $label = isset($_REQUEST['label']) ? $_REQUEST['label'] : '';
        $filtros = isset($_REQUEST['filtros']) ? $_REQUEST['filtros'] : '';
        $filtros = json_decode($filtros, true);

        $currentPage = isset($_REQUEST['currentPage']) ? $_REQUEST['currentPage'] : '';
        $limitBodega = isset($_REQUEST['limit']) ? $_REQUEST['limit'] : '';

        if ($idBodega) {
            # code...
            $limit = '';
            if ($currentPage) {
                (int) $currentPage -= 1;
                $currentPage *= $limitBodega;
                $limit = ' LIMIT ' . $currentPage . ' , ' . $limitBodega;
            }
            $condicion = '';
            if ($filtros && $filtros['Nombre']) {
                $condicion .= ' AND Nombre LIKE "%' . $filtros['Nombre'] . '%"';
            }
            if ($filtros && $filtros['Fecha_Vencimiento']) {

                $condicion .= ' AND Fecha_Vencimiento = "' . $filtros['Fecha_Vencimiento'] . '"';
            }
            if ($filtros && $filtros['Presentacion']) {
                $condicion .= ' AND Presentacion = "' . $filtros['Presentacion'] . '"';
            }

            if ($label) {
                $todos = array('text' => 'TODOS', 'value' => '-1', 'Fecha_Vencimiento' => 'Si', 'Selected' => '0');
                $query = 'SELECT SQL_CALC_FOUND_ROWS Nombre AS text, Id_Grupo_Estiba as value';
            } else {
                $query = 'SELECT SQL_CALC_FOUND_ROWS  Nombre, Id_Grupo_Estiba ';
            }
            $query .= ', Fecha_Vencimiento, Presentacion, false AS Selected
     FROM Grupo_Estiba WHERE Id_Bodega_Nuevo = ' . $idBodega . $condicion . $limit;


            $oCon = new consulta();
            $oCon->setQuery($query);
            $oCon->setTipo('Multiple');

            $grupos = $oCon->getData();
            unset($oCon);


            $query_count = "SELECT COUNT(*)as Cant FROM  Grupo_Estiba WHERE Id_Bodega_Nuevo = $idBodega $condicion";
            $oCon = new consulta();
            $oCon->setQuery($query_count);
            $cant = $oCon->getData()['Cant'];
            unset($oCon);


            $data = [];
            if (count($grupos) > 0) {
                // Reemplazar los valores del primer elemento del array $grupos con los de $todos si $todos está definido
                if ($label == 'true') {
                    // Convertir el primer elemento del array $grupos a un array asociativo
                    $primerGrupoArray = json_decode(json_encode($grupos[0]), true);
                    // Fusionar el array asociativo con $todos
                    $nuevosDatos = (array) $todos + $primerGrupoArray;
                    // Convertir de nuevo a stdClass
                    $grupos[0] = (object) $nuevosDatos;
                }

                $data = $grupos;
            }

            $res['Tipo'] = 'success';
            $res['Grupos'] = $data;
            $res['numReg'] = $cant;
            return $this->success($res);

        } else {
            return $this->success('faltan datos necesarios');
        }
    }

    public function verInventarioContrato()
    {
        $Id_Inventario_Nuevo = isset($_REQUEST['Id_Inventario_Nuevo']) ? $_REQUEST['Id_Inventario_Nuevo'] : '';

        $query = 'SELECT C.Nombre_Contrato, C.Tipo_Contrato,
            IC.Cantidad as Cantidad,
            IC.Cantidad_Apartada,
            IC.Cantidad_Seleccionada,
            SUM(IC.Cantidad - (IC.Cantidad_Apartada + IC.Cantidad_Seleccionada)) AS cantidadContrato
            FROM Inventario_Contrato IC
            INNER JOIN Contrato C ON IC.Id_Contrato = C.Id_Contrato
            WHERE IC.Id_Inventario_Nuevo IN  (' . $Id_Inventario_Nuevo . ')
            GROUP BY IC.Id_Contrato';
        $oCon = new consulta();
        $oCon->setTipo('Multiple');
        $oCon->setQuery($query);
        $contra = $oCon->getData();

        unset($oCon);

        return response()->json($contra);
    }

    public function verApartadas()
    {
        $id = isset($_REQUEST['id_inventario_nuevo']) ? $_REQUEST['id_inventario_nuevo'] : '';

        $query = "SELECT
            R.Id_Remision,
            R.Codigo,
            R.Fecha,
            R.Nombre_Destino AS Destino,
            PR.Cantidad,
            (SELECT F.full_name FROM people F WHERE F.identifier = R.Identificacion_Funcionario) AS Identificacion_Funcionario,
            (CASE WHEN R.Estado_Alistamiento=0 THEN 1 WHEN R.Estado_Alistamiento=1 THEN 2 END) AS Fase
            FROM Producto_Remision PR
            INNER JOIN Remision R ON PR.Id_Remision=R.Id_Remision
            WHERE R.Estado='Pendiente' AND PR.Id_Inventario_Nuevo IN($id) ORDER BY R.Codigo";


        $oCon = new consulta();
        $oCon->setTipo('Multiple');
        $oCon->setQuery($query);
        $resultado = $oCon->getData();
        unset($oCon);


        return response()->json($resultado);
    }

    public function verSeleccionados()
    {
        $id_origen = isset($_REQUEST['Id_Bodega_Nuevo']) ? $_REQUEST['Id_Bodega_Nuevo'] : '';
        $id_producto = isset($_REQUEST['Id_Producto']) ? $_REQUEST['Id_Producto'] : '';

        $query = 'SELECT B.* ,
            (SELECT F.full_name FROM people F
                WHERE B.Id_Funcionario = F.identifier ) AS Identificacion_Funcionario
            FROM Borrador B
            WHERE B.Tipo = "Remision"  AND B.Estado = "Activo"
            ORDER BY Id_Borrador DESC ';
        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->setTipo('Multiple');
        $borradores = $oCon->getData();
        unset($oCon);

        $productos_apartados = [];
        foreach ($borradores as $borrador) {
            $model = json_decode($borrador->Texto, true);
            $Productos = (array) $model['Productos'];
            foreach ($Productos as $Producto) {
                foreach ($Producto['Lotes_Seleccionados'] as $seleccionado) {
                    if ($model['Modelo']) {
                        if ($seleccionado['Id_Producto'] == $id_producto && $model['Modelo']['Id_Origen'] == $id_origen) {
                            $producto_apartado['Codigo'] = $borrador['Codigo'];
                            $producto_apartado['Fecha'] = $borrador['Fecha'];
                            $producto_apartado['Identificacion_Funcionario'] = $borrador['Identificacion_Funcionario'];
                            $producto_apartado['Tipo'] = $model['Modelo']['Tipo'];
                            $producto_apartado['Nombre_Destino'] = $model['Modelo']['Nombre_Destino'];
                            $producto_apartado['Nombre_Origen'] = $model['Modelo']['Nombre_Origen'];
                            $producto_apartado['Cantidad_Seleccionada'] = $seleccionado['Cantidad_Seleccionada'];
                            $producto_apartado['Lote'] = $seleccionado['Lote'];
                            array_push($productos_apartados, $producto_apartado);
                        }
                    }
                }
            }
        }
        return response()->json($productos_apartados);
    }

    public function verCompras()
    {
        $id = isset($_REQUEST['id_producto']) ? $_REQUEST['id_producto'] : '';
        $limit = isset($_REQUEST['limit']) ? $_REQUEST['limit'] : '';
        $cond = '';
        if (!$limit) {
            $cond = "AND AR.Id_Bodega_Nuevo is not null";
            $limit = 3;
        }
        $query = "SELECT AR.Id_Acta_Recepcion as Id_Acta,
            AR.Fecha_Creacion as Fecha,
            AR.Codigo as Codigo_Acta,
            SUM(PAR.Cantidad) as Cantidad, PAR.Precio, OC.Codigo as Codigo_Compra_N, OC.Id_Orden_Compra_Nacional as Id_Compra_N, OCI.Codigo as Codigo_Compra_I, OCI.Id_Orden_Compra_Internacional as Id_Compra_I, P.social_reason as Proveedor,

        (SELECT F.full_name FROM people F WHERE F.identifier=AR.Identificacion_Funcionario) as Funcionario

        FROM Producto_Acta_Recepcion PAR

        INNER JOIN Acta_Recepcion AR ON PAR.Id_Acta_Recepcion=AR.Id_Acta_Recepcion
        LEFT JOIN Orden_Compra_Nacional OC ON OC.Id_Orden_Compra_Nacional = AR.Id_Orden_Compra_Nacional
        LEFT JOIN Orden_Compra_Internacional OCI ON OCI.Id_Orden_Compra_Internacional = AR.Id_Orden_Compra_Internacional
        INNER JOIN third_parties P ON P.id = AR.Id_Proveedor

        WHERE PAR.Id_Producto =$id AND (AR.Estado = 'Aprobada' OR AR.Estado = 'Acomodada')
        $cond
        GROUP BY AR.Id_Acta_Recepcion
        Order BY AR.Fecha_Creacion DESC LIMIT $limit ";

        $oCon = new consulta();
        $oCon->setTipo('Multiple');
        $oCon->setQuery($query);
        $resultado = $oCon->getData();
        unset($oCon);


        return response()->json($resultado);
    }

    public function descargaEtiquetaControlado()
    {
        $id = (isset($_REQUEST['id']) ? $_REQUEST['id'] : '');
        $content = '';

        $query = 'SELECT
            I.Codigo, P.Nombre_Comercial, I.Cantidad, I.Lote, I.Fecha_Vencimiento
            FROM Inventario_Nuevo I
            INNER JOIN Producto P
            ON I.Id_Producto = P.Id_Producto
            WHERE I.Id_Inventario_Nuevo =' . $id;


        $oCon = new consulta();
        $oCon->setTipo('Multiple');
        $oCon->setQuery($query);
        $productos = $oCon->getData();
        unset($oCon);

        /* FUNCIONES BASICAS */
        function fecha($str)
        {
            $parts = explode(" ", $str);
            $date = explode("-", $parts[0]);
            return $date[2] . "/" . $date[1] . "/" . $date[0];
        }
        /* FIN FUNCIONES BASICAS*/

        ob_start(); // Se Inicializa el gestor de PDF
        foreach ($productos as $prod) {
            $nom = $prod->Nombre_Comercial;
            $barras = '' /* generabarras($prod["Codigo"]) */ ;
            $lote = $prod->Lote;
            $fecha = $prod->Fecha_Vencimiento;
            $temp = 'tempimg' . uniqid() . '.jpg';
            $dataURI = $barras;
            /* $dataPieces = explode(',', $dataURI);
            $encodedImg = $dataPieces[1];
            $decodedImg = base64_decode($encodedImg);
            file_put_contents($temp, $decodedImg); */
            $content .= '<page backtop="0mm" backbottom="0mm">
                <div class="page-content" style="width:103mm;height:23mm;" >
                <table style="width:98mm;height:23mm;" cellspacing="0" cellpadding="0">
                <tr>
                <td style="width:50%;">
                <div style="width:50mm;height:22mm;padding:3px;text-align:center;vertical-align:middle;text-transform:uppercase;letter-spacing:1px !important;"><span style="font-size:7px;line-height:8px;"><img src="' . $temp . '"><br>L-' . $lote . '-F.V. ' . $fecha . '<br>' . $prod->Codigo . "<br>" . $nom . '</span>
                </div>
                </td>
                <td style="width:50%">
                <div style="width:50mm;height:22mm;padding:3px;text-align:center;vertical-align:middle;text-transform:uppercase;letter-spacing:1px !important;"><span style="font-size:7px;line-height:8px;"><img src="' . $temp . '"><br>L-' . $lote . "-FV " . $fecha . '<br>' . $prod->Codigo . "<br>" . $nom . '</span>
                </div>
                </td>
                </tr>

                </table>
                </div>
                </page>';
        }
        /* FIN CONTENIDO GENERAL DEL ARCHIVO MEZCLANDO TODA LA INFORMACION*/
        $pdf = Pdf::loadHTML('<h1>Etiquetas Controlados</h1>' . $content);
        return $pdf->stream('etiqueta.pdf');
    }
}

