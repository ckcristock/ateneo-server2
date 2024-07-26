<?php

namespace App\Http\Controllers;

use App\Models\Factura;
use Illuminate\Http\Request;
use App\Http\Services\consulta;
use App\Http\Services\complex;
use App\Models\NotaCreditoGlobal;
use App\Models\Person;
use App\Models\ProductoNotaCreditoGlobal;
use App\Models\ThirdParty;
use App\Traits\ApiResponser;
use Illuminate\Support\Facades\DB;

class FacturaController extends Controller
{
    use ApiResponser;
    public function getNotasCreditos(Request $request)
    {
        $query = NotaCreditoGlobal::query();

        $query->when($request->filled('cod_nota'), function ($q) use ($request) {
            return $q->where('Codigo_Nota', 'like', '%' . $request->input('cod_nota') . '%');
        });
    
        $query->when($request->filled('cliente'), function ($q) use ($request) {
            return $q->whereHas('factura.thirdParty', function ($subQ) use ($request) {
                $subQ->where('is_client', true)->where('name', 'like', '%' . $request->input('cliente') . '%');
            });
        });

        $query->when($request->filled('funcionario'), function ($q) use ($request) {
            return $q->whereHas('factura.person', function ($subQ) use ($request) {
                $subQ->where('name', 'like', '%' . $request->input('funcionario') . '%');
            });
        });

        $query->when($request->filled('cod_factura'), function ($q) use ($request) {
            return $q->whereHas('factura', function ($subQ) use ($request) {
                $subQ->where('Codigo', 'like', '%' . $request->input('cod_factura') . '%');
            });
        });

        $notasCredito = $query->with(['factura.person', 'factura.thirdParty'])
            ->orderBy('Fecha', 'DESC')
            ->paginate(request()->get('pageSize', 10), ['*'], 'page', request()->get('page', 1));

        $notasCredito->getCollection()->transform(function ($nota) {
            $clienteNombre = $nota->factura->thirdParty->first_name ?? '';
            $clienteApellido = $nota->factura->thirdParty->first_surname ?? '';
            $funcionarioNombre = $nota->factura->person->first_name ?? '';
            $funcionarioApellido = $nota->factura->person->first_surname ?? ''; 

            return (object) [
                
                'Codigo_Factura' => $nota->factura->Codigo,
                'Id_Factura' => $nota->Id_Factura,
                'Tipo_Factura' => $nota->Tipo_Factura,
                'Codigo_Nota' => $nota->Codigo,
                'Fecha_Nota' => $nota->Fecha,
                'Id_Nota_Credito_Global' => $nota->Id_Nota_Credito_Global,
                'Cliente' => $clienteNombre . ' ' . $clienteApellido,
                'Funcionario' => $funcionarioNombre . ' ' . $funcionarioApellido,
            ];
        });

        $total = $notasCredito->total();

        $response = $notasCredito->toArray();
        $response['numReg'] = $total;

        return $this->success($response);
    }

    public function listaFacturaClienteNotasCredito()
    {
        $id = (isset($_REQUEST['id']) ? $_REQUEST['id'] : '');
        $modelo = (isset($_REQUEST['modelo']) ? $_REQUEST['modelo'] : '');
        $codigo = (isset($_REQUEST['codigo']) ? $_REQUEST['codigo'] : '');
        $tipoCliente = (isset($_REQUEST['tipoCliente']) ? $_REQUEST['tipoCliente'] : '');

        $joins = $this->joins_db($modelo);
        $where = $this->condicion_db($modelo, $id, $codigo, $tipoCliente);
        $selects = $this->selects_db($modelo);
        $query = $selects . 'FROM  ' . $modelo . ' F' . $joins . $where;
        $oCon = new consulta();
        $oCon->setQuery($query);
        $factura = $oCon->getData();
        unset($oCon);
        if ($factura) {
            $valor_nota = $this->factura_nota_credito($factura['Id_Factura'], $modelo);
            $valor_factura = $this->select_db_productos($factura['Id_Factura'], $modelo);
            if ($valor_factura > $valor_nota) {
                $resultado['tipo'] = 'success';
                $resultado['Factura'] = $factura;
            } else {
                $resultado['tipo'] = 'error';
                $resultado['title'] = 'Factura con nota crédito';
                $resultado['mensaje'] = 'A esta factura ya se le realizó una nota por el valor total de la factura';
            }
        } else {
            $resultado['tipo'] = 'error';
            $resultado['title'] = 'Factura no encotrada';
            $resultado['mensaje'] = 'No se ha encontrada factura asociada a ese código';
        }
        return response()->json($resultado);
    }

    function selects_db($modelo)
    {
        $selects = 'SELECT
        F.Id_' . $modelo . '  AS Id_Factura, F.Codigo as Codigo, F.Nota_Credito ';

        if ($modelo == 'Factura') {
            $selects .= ' , F.Id_Dispensacion';
        }
        return $selects;
    }
    function joins_db($modelo)
    {
        $joins = '';
        if ($modelo == 'Factura_Capita' || $modelo == 'Factura_Administrativa') {
            $joins .= '
              INNER JOIN  Descripcion_' . $modelo . ' PF
              ON PF.Id_' . $modelo . '=F.Id_' . $modelo . ' ';
        } else {
            $joins .= '
              INNER JOIN Producto_' . $modelo . ' PF
              ON PF.Id_' . $modelo . ' = F.Id_' . $modelo;
        }
        return $joins;
    }
    function condicion_db($modelo, $id, $codigo, $tipoCliente)
    {
        $condicion = '
        WHERE F.Id_Cliente=' . $id;

        if ($modelo == 'Factura_Venta') {
            $condicion .= ' AND F.Estado <> "Anulada" AND F.Estado <> "Pagada" ';
        } else {
            $condicion .= ' AND F.Estado_Factura <> "Anulada" AND F.Estado_Factura <> "Pagada" ';
        }
        if ($modelo == 'Factura_Administrativa') {
            $condicion .= 'AND F.Tipo_Cliente ="' . $tipoCliente . '" ';
        }
        $condicion .= ' AND F.Codigo = "' . $codigo . '"';
        return $condicion;
    }
    function factura_nota_credito($id_factura, $modelo)
    {
        $query = 'SELECT Id_Nota_Credito_Global, Codigo FROM Nota_Credito_Global WHERE Tipo_Factura = "' . $modelo . '" AND Id_Factura = ' . $id_factura;
        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->setTipo('Multiple');
        $notas_creditos = $oCon->getData();
        unset($oCon);
        $total_de_notas = 0;
        if ($notas_creditos) {
            # code...
            foreach ($notas_creditos as $nota) {
                # code...

                $query = 'SELECT SUM(Valor_Nota_Credito) AS Total_Nota
                    FROM Producto_Nota_Credito_Global
                    WHERE Id_Nota_Credito_Global = ' . $nota['Id_Nota_Credito_Global'] . '
                    GROUP BY Id_Nota_Credito_Global';
                ;
                $oCon = new consulta();

                $oCon->setQuery($query);
                $valor = $oCon->getData();
                $total_de_notas += $valor['Total_Nota'];
            }
        }
        return $total_de_notas;
    }

    function select_db_productos($id_factura, $modelo)
    {
        $modelo_producto = '';

        if ($modelo == 'Factura_Capita' || $modelo == 'Factura_Administrativa') {
            $modelo_producto = 'Descripcion_' . $modelo;
        } else {
            $modelo_producto = 'Producto_' . $modelo;
        }

        //GENERALES
        $query = 'SELECT  PF.Cantidad,  PF.Descuento, PF.Impuesto,';

        //productos y ids modelo producto


        // seleccionar precio
        if ($modelo_producto == 'Producto_Factura_Venta') {
            # code...
            $query .= 'PF.Precio_Venta AS Precio ';
        } else {
            $query .= 'PF.Precio';
        }
        $query .= ' FROM ' . $modelo_producto . ' PF WHERE Id_' . $modelo . '=' . $id_factura;


        $oCon = new consulta();

        $oCon->setQuery($query);
        $oCon->setTipo('Multiple');
        $productos = $oCon->getData();

        $acumulador = 0;
        foreach ($productos as $producto) {
            $acumulador += $this->calcularSubtotal($producto);
        }
        /*  var_dump($acumulador); */
        return $acumulador;
    }

    function calcularSubtotal($Item)
    {
        $valor_iva = ((float) ($Item->Impuesto) / 100) *
            (((float) ($Item->Cantidad) *
                (float) ($Item->Precio)) -
                ((float) ($Item->Cantidad) *
                    (float) ($Item->Descuento))
            );
        $subtotal = ((float) ($Item->Cantidad) * (float) ($Item->Precio)) - ((float) ($Item->Cantidad) * (float) ($Item->Descuento));
        $resultado = $subtotal + $valor_iva;

        return $resultado;
    }

    function validarExistenciaNotaGlobal($id, $response)
    {
        $oItem = new complex('Factura_Venta', 'Id_Factura_Venta', $id);
        $factura = $oItem->getData();
        unset($oItem);

        $query = ' SELECT GROUP_CONCAT( Codigo )   AS Codigos
                    FROM Nota_Credito_Global
                   WHERE Codigo_Factura = "' . $factura['Codigo'] . '"
                   GROUP BY Codigo_Factura';
        $oCon = new consulta();
        $oCon->setQuery($query);
        $notas_globales = $oCon->getData();

        if (!$notas_globales) {
            # code...
            return false;
        }
        $response['type'] = 'error';
        $response['title'] = 'OOPS! Existe Nota Crédito creada para esta factura';
        $response['message'] = 'Se ha realizado nota credito tipo precio (NO AFECTA INVENTARIO) con anterioridad : ' . $notas_globales['Codigos'];

        return true;
    }
    function GetProductosNotaCreditoFactura($idFactura)
    {
        $query = '
                  SELECT
                      IFNULL(GROUP_CONCAT(Id_Producto), 0) AS Excluir_Productos
                  FROM Nota_Credito NC
                  INNER JOIN Producto_Nota_Credito PNC ON NC.Id_Nota_Credito = PNC.Id_Nota_Credito
                  WHERE
                      NC.Id_Factura = ' . $idFactura . ' AND NC.Estado!="Anulada" ';

        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->setTipo('simple');
        $productos_nota_credito = $oCon->getData();
        unset($oCon);

        return $productos_nota_credito['Excluir_Productos'];
    }

    public function listaProductoNotasCredito()
    {
        $id = (isset($_REQUEST['id']) ? $_REQUEST['id'] : '');

        $response = [];
        if ($id == '') {
            echo json_encode(array());
            return;
        }

        if (!$this->validarExistenciaNotaGlobal($id, $response)) {


            $productos_excluir = $this->GetProductosNotaCreditoFactura($id);

            $condicion_productos_excluir = ' HAVING Cantidad > 0';


            $query2 = 'SELECT PFV.*,
                IFNULL(CONCAT(P.Nombre_Comercial, " - ",P.Principio_Activo, " ", P.Cantidad,"", P.Unidad_Medida, " " , P.Presentacion, "\n", P.Invima, " CUM:", P.Codigo_Cum),
                CONCAT(P.Nombre_Comercial, " LAB-", P.Laboratorio_Comercial)) as producto,
                P.Id_Producto,
                IF(P.Laboratorio_Generico IS NULL,P.Laboratorio_Comercial,P.Laboratorio_Generico) as Laboratorio,
                P.Presentacion,
                P.Codigo_Cum as Cum,
                PFV.Fecha_Vencimiento as Vencimiento,
                PFV.Lote as Lote,
                "true" as Disabled, 0 as Subtotal_Nota, 0 as Iva,
                (PFV.Cantidad - (SELECT IFNULL(SUM(PNC.Cantidad), 0) FROM Producto_Nota_Credito PNC INNER JOIN Nota_Credito NC ON PNC.Id_Nota_Credito = NC.Id_Nota_Credito WHERE NC.Id_Factura = PFV.Id_Factura_Venta AND PNC.Id_Producto = PFV.Id_Producto AND PNC.Lote = PFV.Lote AND NC.Estado!="Anulada")) AS Cantidad
                FROM Producto_Factura_Venta PFV

                LEFT JOIN Producto P ON P.Id_Producto = PFV.Id_Producto
                /* WHERE PFV.Id_Factura_Venta = */' /* . $id . $condicion_productos_excluir */ ;

            $oCon = new consulta();
            $oCon->setQuery($query2);
            $oCon->setTipo('Multiple');
            $productos = $oCon->getData();
            unset($oCon);

            if (count($productos) == 0) {
                $query22 = 'SELECT PFV.*,
        IFNULL(CONCAT(P.Principio_Activo, " ", P.Cantidad,"", P.Unidad_Medida, " " , P.Presentacion, "\n", P.Invima, " CUM:", P.Codigo_Cum), CONCAT(P.Nombre_Comercial, " LAB-", P.Laboratorio_Comercial)) as producto,
        P.Id_Producto,
        IF(P.Laboratorio_Generico IS NULL,P.Laboratorio_Comercial,P.Laboratorio_Generico) as Laboratorio,
        P.Presentacion,
        P.Codigo_Cum as Cum,
        PFV.Fecha_Vencimiento as Vencimiento,
        PFV.Lote as Lote,
        PFV.Id_Inventario_Nuevo as Id_Inventario_Nuevo,
        PFV.Precio_Venta as Costo_unitario,
        PFV.Cantidad as Cantidad,
        PFV.Precio_Venta as PrecioVenta,
        PFV.Subtotal as Subtotal,
        PFV.Id_Producto_Factura_Venta as idPFV,"true" as Disabled, 0 as Subtotal_Nota, 0 as Iva,
        (PFV.Cantidad - (SELECT IFNULL(SUM(PNC.Cantidad), 0) FROM Producto_Nota_Credito PNC INNER JOIN Nota_Credito NC ON PNC.Id_Nota_Credito = NC.Id_Nota_Credito WHERE NC.Id_Factura = PFV.Id_Factura_Venta AND PNC.Id_Producto = PFV.Id_Producto AND PNC.Lote = PFV.Lote)) AS Cantidad
        FROM Producto_Factura_Venta PFV
        LEFT JOIN Producto P ON PFV.Id_Producto = P.Id_Producto
        WHERE PFV.Id_Factura_Venta =' . $id . $condicion_productos_excluir;

                $oCon = new consulta();
                $oCon->setQuery($query22);
                $oCon->setTipo('Multiple');
                $productos = $oCon->getData();
                unset($oCon);
            }

            $response['type'] = 'success';
            $response['data'] = $productos;
        }

        return response()->json($response);
    }

    public function guardarNotaCredito()
    {
        $mod = (isset($_REQUEST['modulo']) ? $_REQUEST['modulo'] : '');
        $datos = (isset($_REQUEST['datos']) ? $_REQUEST['datos'] : '');
        $productos = (isset($_REQUEST['productos']) ? $_REQUEST['productos'] : '');

        $datos = (array) json_decode($datos);
        $productos = (array) json_decode($productos, true);



        //$cod= $configuracion->getConsecutivo('Nota_Credito','Nota_Credito');

        $cod = '45645646456456'; //generarConsecutivo()

        // $query = 'SELECT E.Id_Bodega_Nuevo
        //     FROM Inventario_Nuevo I
        //     INNER JOIN Estiba E ON E.Id_Estiba = I.Id_Estiba
        //     WHERE I.Id_Inventario_Nuevo = ' . $productos[0]['Id_Inventario_Nuevo'];
        // $oCon = new consulta();
        // $oCon->setQuery($query);
        // $id_bodega_nuevo = $oCon->getData();
        // $id_bodega_nuevo = $id_bodega_nuevo['Id_Bodega_Nuevo'];


        $datos['Codigo'] = $cod;

        $oItem = new complex($mod, "Id_" . $mod);
        foreach ($datos as $index => $value) {
            $oItem->$index = $value;
        }
        $oItem->Id_Bodega_Nuevo = 0;
        $oItem->save();
        $id_venta = $oItem->getId();
        $resultado = array();
        unset($oItem);

        /* AQUI GENERA QR */
        //$qr = generarqr('notascredito', $id_venta, '/IMAGENES/QR/');
        $oItem = new complex("Nota_Credito", "Id_Nota_Credito", $id_venta);
        //$oItem->Codigo_Qr = $qr;
        $oItem->save();
        unset($oItem);
        /* HASTA AQUI GENERA QR */

        foreach ($productos as $producto) {
            if ($producto['Nota']) {
                $oItem = new complex('Producto_Nota_Credito', "Id_Producto_Nota_Credito");
                $producto["Id_Nota_Credito"] = $id_venta;
                foreach ($producto as $index => $value) {
                    $oItem->$index = $value;
                }
                $oItem->Id_Inventario = 0;
                $oItem->Id_Inventario_Nuevo = 0;
                $subtotal = $producto['Cantidad_Ingresada'] * $producto['Precio_Venta'];
                $oItem->Cantidad = $producto['Cantidad_Ingresada'];
                $oItem->Subtotal = number_format($subtotal, 2, ".", "");
                $oItem->Id_Motivo = $producto['Id_Motivo'];
                $oItem->save();
                unset($oItem);
            }
        }
        if ($id_venta != "") {
            $resultado['mensaje'] = "Se ha guardado correctamente la nota credito con codigo: " . $datos['Codigo'];
            $resultado['tipo'] = "success";
        } else {
            $resultado['mensaje'] = "ha ocurrido un error guardando la informacion, por favor verifique";
            $resultado['tipo'] = "error";
        }

        return response()->json($resultado);
    }

    public function getNotaCredito()
    {
        $id_nota = request()->input('id_nota_credito', '');

        if ($id_nota) {
            $nota_credito = NotaCreditoGlobal::where('Id_Nota_Credito_Global', $id_nota)->first();

            if ($nota_credito) {
                $descripciones_nota = ProductoNotaCreditoGlobal::leftJoin('Causal_No_Conforme', 'Causal_No_Conforme.Id_Causal_No_Conforme', '=', 'Producto_Nota_Credito_Global.Id_Causal_No_Conforme')
                    ->where('Producto_Nota_Credito_Global.Id_Nota_Credito_Global', $nota_credito->Id_Nota_Credito_Global)
                    ->select('Producto_Nota_Credito_Global.*', DB::raw('((Producto_Nota_Credito_Global.Impuesto)/100) * ( Producto_Nota_Credito_Global.Cantidad * (Producto_Nota_Credito_Global.Precio_Nota_Credito) ) as Total_Impuesto'), 'Causal_No_Conforme.Nombre AS Motivo')
                    ->get();

                $nota_credito->Factura = Factura::where('Id_Factura', $nota_credito->Id_Factura)
                    ->select('Id_Factura', 'Fecha_Documento', 'Id_Cliente', 'Codigo', 'Estado_Factura', 'Condicion_Pago', 'Fecha_Pago')
                    ->first();

                if ($nota_credito->Tipo_Factura == 'Factura_Administrativa') {
                    $tipoCliente = $nota_credito->Factura->Tipo_Cliente;
                } else {
                    $tipoCliente = 'Cliente';
                }

                $nota_credito->Cliente = $this->queryClientesFacturaAdministrativa($tipoCliente, $nota_credito->Factura->Id_Cliente);

                $response['Nota_Credito'] = $nota_credito;
                $response['Productos_Nota'] = $descripciones_nota;

                return response()->json($response);
            }
        }
    }


    function queryClientesFacturaAdministrativa($tipoCliente, $id_cliente)
    {
        $query = '';

        if ($tipoCliente == 'Funcionario') {
            $cliente = Person::find($id_cliente);
            $query = [
                'Nombre_Cliente' => $cliente->first_name . ' ' . $cliente->first_surname,
                'Id_Cliente' => $cliente->id,
                'Direccion_Cliente' => $cliente->address,
                'Telefono' => $cliente->phone ?? $cliente->cellphone,
                'Ciudad_Cliente' => '',
                'Condicion_Pago' => '1'
            ];
        } else if ($tipoCliente == 'Cliente') {
            $cliente = ThirdParty::where('is_client', true)->find($id_cliente);
            $query = [
                'Nombre_Cliente' => $cliente->first_name ? $cliente->first_name . ' ' . $cliente->first_surname : $cliente->social_reason,
                'Id_Cliente' => $cliente->id,
                'Direccion_Cliente' => $cliente->cod_dian_address,
                'Telefono' => $cliente->phone_payments ?? $cliente->cell_phone,
                'Ciudad_Cliente' => $cliente->municipality->name,
                'Condicion_Pago' => $cliente->condition_payment ?? '1'
            ];
        } else if ($tipoCliente == 'Proveedor') {
            $cliente = ThirdParty::where('is_supplier', true)->find($id_cliente);
            $query = [
                'Nombre_Cliente' => $cliente->first_name ? $cliente->first_name . ' ' . $cliente->first_surname : $cliente->social_reason,
                'Id_Cliente' => $cliente->id,
                'Direccion_Cliente' => $cliente->cod_dian_address,
                'Telefono' => $cliente->landline ?? $cliente->cell_phone,
                'Ciudad_Cliente' => $cliente->municipality->name,
                'Condicion_Pago' => $cliente->condition_payment ?? '1'
            ];
        }

        return $query;
    }

    public function getNotasCreditoPorFactura(Request $request)
    {
        $id_factura = $request->input('id_factura', ''); // Obtener el valor de 'id_factura' o un valor por defecto si no está presente
        $tipo_factura = $request->input('tipo_factura', '');
        if ($id_factura) {
            $query = 'SELECT * FROM Nota_Credito_Global
            WHERE Id_Factura = ' . $id_factura . ' AND  Tipo_Factura = "' . $tipo_factura . '"';
            $oCon = new consulta();

            $oCon->setQuery($query);
            $oCon->setTipo('Multiple');
            $notas_credito = $oCon->getData();
            unset($oCon);

            foreach ($notas_credito as $key => $nota_credito) {
                # code...
                // $notas_credito[$key]['Observaciones'] = utf8_decode($notas_credito['Observaciones']);
                if ($nota_credito) {
                    /*  $nota_credito['Observaciones'] = utf8_decode($nota_credito['Observaciones'] ); */

                    $query = ' SELECT P.* , C.Nombre AS Motivo FROM Producto_Nota_Credito_Global P
                         LEFT JOIN Causal_No_Conforme C ON C.Id_Causal_No_Conforme = P.Id_Causal_No_Conforme
                      WHERE P.Id_Nota_Credito_Global = ' . $nota_credito->Id_Nota_Credito_Global;
                    $oCon = new consulta();
                    $oCon->setTipo('Multiple');
                    $oCon->setQuery($query);
                    $notas_credito[$key]->Productos_Nota = $oCon->getData();
                    unset($oCon);

                }

            }
            #Factura datos
            $tercero = 'Cliente';
            if ($notas_credito[0]->Tipo_Factura == 'Documento_No_Obligados') {
                $tercero = 'Proveedor';
            }
            $query = "SELECT Id_".$notas_credito[0]->Tipo_Factura." AS Id_Factura, Codigo , Fecha_Documento,  Id_$tercero";

            if ($notas_credito[0]->Tipo_Factura == 'Factura_Administrativa' || $notas_credito[0]->Tipo_Factura == 'Documento_No_Obligados') {
                #
                $query .= ", Tipo_$tercero ";

            }

            #dato factura
            $query .= ' FROM ' . $tipo_factura . '
     WHERE Id_' . $tipo_factura . ' = ' . $id_factura;
            $oCon = new consulta();
            $oCon->setQuery($query);

            $factura = $oCon->getData();

            unset($oCon);

            

            #dato cliente

            if ($tipo_factura == 'Factura_Administrativa') {
                #
                $query = $this->queryClientesFacturaAdministrativa($factura['Tipo_Cliente'], $factura['Id_Cliente']);
            } else if ($tipo_factura == 'Documento_No_Obligados') {

                $query = $this->queryClientesFacturaAdministrativa($factura['Tipo_Proveedor'], $factura['Id_Proveedor']);
            } else {
                $query = $this->queryClientesFacturaAdministrativa('Cliente', $factura['Id_Cliente']);
            }

            #dato factura

            $oCon = new consulta();
            $oCon->setQuery($query);
            // return $query;
            // $cliente = $oCon->getData();
            $cliente = $query;
            unset($oCon);
            $response['Notas'] = $notas_credito;
            $response['Cliente'] = $cliente;
            $response['Factura'] = $factura;
            return $this->success($response);
        }
    }


}
