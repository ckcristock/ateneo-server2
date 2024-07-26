<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use App\Http\Services\consulta;
use App\Models\ActaRecepcion;
use App\Models\OrdenCompraNacional;
use App\Models\Product;
use App\Models\ProductoOrdenCompraNacional;
use Illuminate\Support\Facades\DB;

class OrdenCompraNacionalController extends Controller
{
    use ApiResponser;

    public function listarPendientes(Request $request)
    {
        $resultado = OrdenCompraNacional::with('person', 'third', 'acta')
            ->whereNotIn('Estado', ['Recibida', 'Anulada'])
            ->where('Aprobacion', '=', 'Aprobada')
            ->where('Id_Bodega_Nuevo', '<>', 0)
            ->whereNotIn('Id_Orden_Compra_Nacional', function ($query) {
                $query->select('id_orden_compra')
                    ->from('orden_compra_nacional_purchase_request');
            })
            ->withCount(['products as Items'])
            ->when($request->codigo, function ($q, $fill) {
                $q->where('Codigo', 'like', "%$fill%");
            })
            ->when($request->proveedor, function ($q, $fill) {
                $q->whereHas('third', function ($query) use ($fill) {
                    $query->where(DB::raw('IFNULL(social_reason, CONCAT_WS(" ", first_name, first_surname))'), 'like', "%$fill%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->orderBy('Codigo', 'desc')
            ->paginate(Request()->get('pageSize', 10), ['*'], 'page', Request()->get('page', 1));
        //!Si se van a usar comoras internacionales aquí hay un ejemplo de como quedaría, no existen modelos aun
        /* $resultados = OrdenCompraInternacional::with(['funcionario', 'proveedor'])
            ->whereNotIn('Estado', ['Recibida', 'Anulada'])
            ->orderBy('Fecha_Registro', 'desc')
            ->orderBy('Codigo', 'desc')
            ->withCount(['productosOrdenCompraInternacional as Items'])
            ->get(); */

        return $this->success($resultado);
    }

    public function actaRecepcionCompra(Request $request)
    {
        $ordenCompra = OrdenCompraNacional::with(
            'products',
            'third',
            'store',
            'products.product.category.categoryVariables',
            'products.product.subcategory.subcategoryVariables'
        )->find($request->id);
    
        $ordenCompra->products->map(function ($product) {
            $product->variables = collect(); // Inicializa como colección vacía
    
            if ($product->product->category || $product->product->subcategory) {
                $categoryVariables = $product->product->category
                    ? $product->product->category->categoryVariables->where('reception', true)
                    : collect();
    
                $subcategoryVariables = $product->product->subcategory
                    ? $product->product->subcategory->subcategoryVariables->where('reception', true)
                    : collect();
    
                $product->variables = $categoryVariables->merge($subcategoryVariables);
            }
    
            return $product;
        });
    
        return $this->success($ordenCompra);

        /* $orden = $request->orden;
        $codigo = $request->codigo;
        $tipoCompra = (isset($_REQUEST['compra']) ? $_REQUEST['compra'] : '');
        $query = 'SELECT  COUNT(*) as Total_Items
                        FROM Producto_Orden_Compra_Nacional POCN
                        INNER JOIN Producto P ON P.Id_Producto = POCN.Id_Producto
                        INNER JOIN Orden_Compra_Nacional OCN ON OCN.Id_Orden_Compra_Nacional = POCN.Id_Orden_Compra_Nacional
                        WHERE OCN.Codigo ="' . $codigo . '"';

        $query1 = "SELECT 'Nacional' AS Tipo,
                        OCN.Id_Orden_Compra_Nacional AS Id_Orden_Compra,
                        OCN.Codigo, OCN.Identificacion_Funcionario,
                        OCN.Id_Bodega_Nuevo,
                        (SELECT P.Nombre FROM Proveedor P WHERE P.Id_Proveedor=OCN.Id_Proveedor) AS Proveedor,
                        OCN.Id_Proveedor,
                        BN.Nombre AS 'Nombre_Bodega'
                        FROM Orden_Compra_Nacional OCN
                        INNER JOIN Bodega_Nuevo BN ON BN.Id_Bodega_Nuevo = OCN.Id_Bodega_Nuevo
                        WHERE Codigo = '" . $codigo . "'";

        $query2 = ' SELECT GROUP_CONCAT(POCN.Id_Producto) as Id_Producto,
                        POCN.Id_Orden_Compra_Nacional as Id_Orden_Nacional
                        FROM Producto_Orden_Compra_Nacional POCN
                        INNER JOIN Producto P ON P.Id_Producto = POCN.Id_Producto
                        INNER JOIN Orden_Compra_Nacional OCN ON OCN.Id_Orden_Compra_Nacional = POCN.Id_Orden_Compra_Nacional
                        WHERE OCN.Codigo ="' . $codigo . '"';

        $oCon = new consulta();
        $oCon->setQuery($query);
        $res = $oCon->getData();
        unset($oCon);


        $oCon = new consulta();
        $oCon->setQuery($query1);
        $res1 = $oCon->getData();
        unset($oCon);

        $oCon = new consulta();
        $oCon->setQuery($query2);
        $id_productos = $oCon->getData();
        unset($oCon);

        $resultado['encabezado'] = $res1;
        $resultado['Items'] = $res['Total_Items'];
        $resultado['Productos'] = $id_productos;
        $query_retenciones = 'SELECT
            P.retefuente_account_id,
            (IF(P.retefuente_account_id IS NULL OR P.retefuente_account_id = 0, "", (SELECT Nombre FROM Plan_Cuentas WHERE Id_Plan_Cuentas = P.retefuente_account_id))) AS Nombre_Retefuente,
            (IF(P.retefuente_account_id IS NULL OR P.retefuente_account_id = 0, "0", (SELECT Porcentaje FROM Retencion WHERE Id_Plan_Cuenta = P.retefuente_account_id))) AS Porcentaje_Retefuente,
            (IF(P.retefuente_account_id IS NULL OR P.retefuente_account_id = 0, "0", (SELECT Id_Retencion FROM Retencion WHERE Id_Plan_Cuenta = P.retefuente_account_id))) AS Id_Retencion_Fte,
            P.reteica_type,
            P.reteica_account_id,
            (IF(P.reteica_account_id IS NULL OR P.reteica_account_id = 0, "", (SELECT Nombre FROM Plan_Cuentas WHERE Id_Plan_Cuentas = P.reteica_account_id))) AS Nombre_Reteica,
            (IF(P.reteica_account_id IS NULL OR P.reteica_account_id = 0, "0", (SELECT Porcentaje FROM Retencion WHERE Id_Plan_Cuenta = P.reteica_account_id))) AS Porcentaje_Reteica,
            (IF(P.retefuente_account_id IS NULL OR P.retefuente_account_id = 0, "0", (SELECT Id_Retencion FROM Retencion WHERE Id_Plan_Cuenta = P.reteica_account_id))) AS Id_Retencion_Ica,
            P.reteiva_account_id,
            (IF(P.reteiva_account_id IS NULL OR P.reteiva_account_id = 0, "", (SELECT Nombre FROM Plan_Cuentas WHERE Id_Plan_Cuentas = P.reteiva_account_id))) AS Nombre_Reteiva,
            (IF(P.reteiva_account_id IS NULL OR P.reteiva_account_id = 0, "0", (SELECT Porcentaje FROM Retencion WHERE Id_Plan_Cuenta = P.reteiva_account_id))) AS Porcentaje_Reteiva,
            (IF(P.retefuente_account_id IS NULL OR P.retefuente_account_id = 0, "0", (SELECT Id_Retencion FROM Retencion WHERE Id_Plan_Cuenta = P.reteiva_account_id))) AS Id_Retencion_Iva,
            regime
            FROM third_parties P
            WHERE id = "' . $res1['Id_Proveedor'] . '"';

        $oCon = new consulta();
        $oCon->setQuery($query_retenciones);
        $retenciones_proveedor = $oCon->getData();
        unset($oCon);

        $resultado['Data_Retenciones'] = $retenciones_proveedor;

        $query_configuracion = 'SELECT
            Valor_Unidad_Tributaria,
            Base_Retencion_Compras_Reg_Comun,
            Base_Retencion_Compras_Reg_Simpl,
            Base_Retencion_Compras_Ica,
            Base_Retencion_Iva_Reg_Comun
            FROM Configuracion
            WHERE Id_Configuracion = 1';

        $oCon = new consulta();
        $oCon->setQuery($query_configuracion);
        $valores_retenciones = $oCon->getData();
        unset($oCon);

        $resultado['Valores_Base_Retenciones'] = $valores_retenciones;

        return response()->json($resultado); */
    }

    public function codigoBarras(Request $request)
    {
        $codigo = request()->input('codigo');
        $orden = request()->input('orden');
        $productos = Product::with('packaging')->where('Codigo_Barras', 'like', "$codigo%")->get();
        if ($productos->isEmpty()) {
            return response()->json([]);
        }
        $producto = $productos->first();
        $ordenCompraNacional = OrdenCompraNacional::where('Codigo', $orden)->first();
        $productoOrdenCompraNacional = ProductoOrdenCompraNacional::where('Id_Producto', $producto->Id_Producto)
            ->when($ordenCompraNacional, function ($query) use ($ordenCompraNacional) {
                return $query->where('Id_Orden_Compra_Nacional', $ordenCompraNacional->Id_Orden_Compra_Nacional);
            })
            ->first();
        $nombreProducto = $producto->Nombre_Comercial;
        $resultado = [
            'Id_Producto' => $producto->Id_Producto,
            'Diferente' => $productoOrdenCompraNacional && $productoOrdenCompraNacional->Id_Producto_Orden_Compra_Nacional != $producto->Id_Producto ? true : false,
            'producto' => [
                [
                    'Nombre_Producto' => $nombreProducto,
                    'Foto' => $producto->Imagen,
                    'Eliminado' => 'No',
                    'Id_Categoria' => $producto->Id_Categoria,
                    'Id_Subcategoria' => $producto->Id_Subcategoria,
                    'Id_Producto' => $producto->Id_Producto,
                    'Nombre_Comercial' => $producto->Nombre_Comercial,
                    'Id_Producto_Orden_Compra' => $productoOrdenCompraNacional ? $productoOrdenCompraNacional->Id_Producto_Orden_Compra_Nacional : 0,
                    'CantidadProducto' => $productoOrdenCompraNacional ? $productoOrdenCompraNacional->Cantidad : 0,
                    'CostoProducto' => $productoOrdenCompraNacional ? $productoOrdenCompraNacional->Total : 0,
                    'Embalaje_id' => $producto->Embalaje_id,
                    'Embalaje' => $producto->packaging->name,
                    'Impuesto' => $producto->impuesto_id == 2 ? 19 : 0,
                    'Precio' => '0',
                    'Cantidad' => '0',
                    'Fecha_Vencimiento' => '',
                    'Subtotal' => 0,
                    'Lote' => '',
                    'Factura' => '',
                    'Codigo_Compra' => $ordenCompraNacional ? $ordenCompraNacional->Codigo : '',
                    'Required' => true,
                    'No_Conforme' => 0,
                ]
            ],
        ];
        return response()->json($resultado);
    }
}
