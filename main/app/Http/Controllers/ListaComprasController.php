<?php

namespace App\Http\Controllers;

use App\Http\Services\complex;
use App\Http\Services\Configuracion;
use App\Http\Services\consulta;
use App\Models\ActividadOrdenCompra;
use App\Models\Company;
use App\Models\OrdenCompraNacional;
use App\Models\Perfil;
use App\Models\Person;
use App\Models\PreCompra;
use App\Models\Product;
use App\Models\ProductoOrdenCompraNacional;
use Illuminate\Http\Request;
use App\Models\ThirdParty;
use App\Models\TipoRechazo;
use App\Traits\ApiResponser;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ListaComprasController extends Controller
{
    use ApiResponser;

    public function guardarCompraNacionalPendientes()
    {
        $userID = auth()->user()->person_id;

        $mod = (isset($_REQUEST['modulo']) ? $_REQUEST['modulo'] : '');
        $datos = (isset($_REQUEST['datos']) ? $_REQUEST['datos'] : '');
        $productos = (isset($_REQUEST['productos']) ? $_REQUEST['productos'] : '');
        $id_no_conforme = (isset($_REQUEST['Id_No_Conforme']) ? $_REQUEST['Id_No_Conforme'] : '');
        $datos = (array) json_decode($datos, true);
        $productos = (array) json_decode($productos, true);
        $cod = generateConsecutive('Orden_Compra_Nacional');
        sumConsecutive('Orden_Compra_Nacional');
        $datos['Codigo'] = $cod;
        $datos['Identificacion_Funcionario'] = $userID;
        $datos['Id_Proveedor'] = $datos['Proveedor'];
        if ($id_no_conforme > "0") {
            $query = "SELECT OC.Codigo AS OC, NC.Estado, NC.Id_No_Conforme, OC.Id_Orden_Compra_Nacional
		From No_Conforme NC
		Inner Join Acta_Recepcion AC ON AC.Id_Acta_Recepcion = NC.Id_Acta_Recepcion_Compra
		INNER JOIN Orden_Compra_Nacional OC ON OC.Id_Orden_Compra_Nacional = AC.Id_Orden_Compra_Nacional Where NC.Id_No_Conforme = $id_no_conforme";
            $oCon = new consulta();
            $oCon->setQuery($query);
            $no_conforme = $oCon->getData();
        }
        $i = 0;
        if ($no_conforme['Estado'] != 'Cerrado') {
            $oItem = new complex($mod, "Id_" . $mod);
            foreach ($datos as $index => $value) {
                $i++;
                $oItem->$index = $value;
            }
            $oItem->save();
            $id_venta = $oItem->getId();
            $resultado = array();
            unset($oItem);
            $qr = generarqr('ordencompranacional', $id_venta, 'IMAGENES/QR/');
            $oItem = new complex("Orden_Compra_Nacional", "Id_Orden_Compra_Nacional", $id_venta);
            $oItem->Codigo_Qr = $qr;
            $oItem->save();
            unset($oItem);
            foreach ($productos as $producto) {
                $pnc = new complex('Producto_No_Conforme', 'Id_Producto_No_Conforme', $producto['Id_Producto_No_Conforme']);
                $pnc->Cantidad_Nueva_Orden = (int) $pnc->Cantidad_Nueva_Orden + $producto['Cantidad'];
                $pnc->save();
                unset($pnc);
                $oItem = new complex('Producto_Orden_Compra_Nacional', "Id_Producto_Orden_Compra_Nacional");
                $producto["Id_Orden_Compra_Nacional"] = $id_venta;
                foreach ($producto as $index => $value) {
                    $oItem->$index = $value;
                }
                $oItem->Costo = number_format($producto['Costo'], 2, '.', '');
                $oItem->Iva = $producto['Valor_Iva'] == '' ? '0' : number_format($producto['Valor_Iva'], 0, '', '');
                $oItem->save();
                unset($oItem);
            }
            $query = "SELECT SUM(if(Cantidad_Nueva_Orden> Cantidad, 0, Cantidad-Cantidad_Nueva_Orden)) as TotalPendiente from Producto_No_Conforme Where Id_No_Conforme = $id_no_conforme";
            $nc = new consulta();
            $nc->setQuery($query);
            $nc = $nc->getData();
            if ($nc['TotalPendiente'] <= 0) {
                $oItem = new complex('No_Conforme', 'Id_No_Conforme', $id_no_conforme);
                $oItem->Estado = 'Cerrado';
                $oItem->save();
                unset($oItem);
            }
            $oItem = new complex('Actividad_Orden_Compra', "Id_Actividad_Orden_Compra");
            $oItem->Id_Orden_Compra_Nacional = $id_venta;
            $oItem->Identificacion_Funcionario = $userID;
            $oItem->Detalles = "Se creo la orden de compra con codigo $datos[Codigo] por Faltantes de la orden $no_conforme[OC]";
            $oItem->Fecha = date("Y-m-d H:i:s");
            $oItem->Estado = "Creacion";
            $oItem->save();
            unset($oItem);
            $oItem = new complex('Actividad_Orden_Compra', "Id_Actividad_Orden_Compra");
            $oItem->Id_Orden_Compra_Nacional = $no_conforme['Id_Orden_Compra_Nacional'];
            $oItem->Identificacion_Funcionario = $userID;
            $oItem->Detalles = "Se creo la orden de compra con codigo $datos[Codigo] por Faltantes de la orden ";
            $oItem->Fecha = date("Y-m-d H:i:s");
            $oItem->Estado = "Creacion";
            $oItem->save();
            unset($oItem);
            if ($id_venta != "") {
                $resultado['mensaje'] = "Se ha guardado correctamente la orden de compra: " . $datos['Codigo'];
                $resultado['tipo'] = "success";
            } else {
                $resultado['mensaje'] = "ha ocurrido un error guardando la informacion, por favor verifique";
                $resultado['tipo'] = "error";
            }
        } else {
            $resultado['mensaje'] = "ha ocurrido un error guardando la informacion, por favor verifique";
            $resultado['tipo'] = "error";
        }
        return response()->json($resultado);
    }

    public function proveedorBuscar()
    {
        $query = 'SELECT PR.id as value, IFNULL(PR.social_reason, CONCAT_WS(" ", PR.first_name, PR.first_surname)) as text
           FROM third_parties PR WHERE PR.is_supplier=1 and PR.state="Activo"';
        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->setTipo('Multiple');
        $proveedorBuscar = $oCon->getData();
        unset($oCon);
        return $this->success($proveedorBuscar);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    protected $proveedores;

    public function __construct()
    {
        $this->proveedores = ThirdParty::name("social_reason")->whereRaw("is_supplier = 1");
    }

    public function index()
    {
        return $this->success('');
    }

    public function paginate(Request $request)
    {
        return $this->success(
            OrdenCompraNacional::with('person', 'third')
                ->when($request->est, function ($q, $fill) {
                    $q->where('Estado', $fill);
                })
                ->when($request->cod, function ($q, $fill) {
                    $q->where('Codigo', 'like', "%$fill%");
                })
                ->when($request->prov, function ($q, $fill) {
                    $q->select('social_reason as full_name')
                        ->where('social_reason', 'like', '%' . $fill . '%');
                })
                ->when($request->endDate, function ($q, $fill) use ($request) {
                    $q->whereBetween(DB::raw("DATE_FORMAT(Fecha, '%Y-%m-%d')"), [$request->startDate, $fill]);
                })
                ->when($request->func, function ($q, $fill) {
                    $q->whereHas('person', function ($query) use ($fill) {
                        $query->where('first_name', 'like', "%$fill%");
                    });
                })
                ->orderByDesc("created_at")
                ->paginate(request()->get('pageSize', 5), ['*'], 'page', request()->get('page', 1))
        );
    }

    public function getProducts(Request $request)
    {

        $productos = Product::with('unit', 'packaging', 'tax')
            ->when($request->search, function ($query, $fill) {
                $query->where('Nombre_Comercial', 'like', "%$fill%");
            })
            ->when($request->category_id, function ($query, $fill) {
                $query->where('Id_Categoria', $fill);
            })
            ->get(['*', 'Nombre_Comercial as name'])->take(10);

        [$productosConVariables, $variablesLabels] = $this->getVariablesProductosConMismosCampos($productos);
        return $this->success(
            [
                'productos' => $productosConVariables,
                'variables' => $variablesLabels,
            ]
        );
    }

    function getVariablesProductosConMismosCampos($productos)
    {

        [$productos, $variablesLabels] = getVariablesProductos($productos);

        $productosArray = [];

        foreach ($productos as $producto) {
            $productoArray = $producto->toArray();
            $productoArray['variables'] = $producto->variables;
            $productosArray[] = $productoArray;
        }

        // Asegurar que todos los productos tengan todos los labels
        foreach ($productosArray as &$producto) {
            foreach ($variablesLabels as $label) {
                if (!array_key_exists($label, $producto['variables'])) {
                    $producto['variables'][$label] = '';
                }
            }
        }

        return [$productosArray, $variablesLabels];
    }

    public function datosComprasNacionales(Request $request)
    {
        $query = OrdenCompraNacional::with('products', 'person', 'third', 'store', 'activity')
            ->find($request->id);
        return $this->success($query);
    }

    public function actividadOrdenCompra()
    {
        $query = DB::table("Actividad_Orden_Compra as AR")
            ->select([
                "AR.*",
                "p.image as Imagen",
                DB::raw("(CASE
                WHEN AR.Estado='Creacion' THEN CONCAT('1 ',AR.Estado)
                WHEN AR.Estado='Edicion' THEN CONCAT('2 ',AR.Estado)
                WHEN AR.Estado='Recepcion' THEN CONCAT('3 ',AR.Estado)
                WHEN AR.Estado='Aprobacion' THEN CONCAT('4 ',AR.Estado)
                WHEN AR.Estado='Anulada' THEN CONCAT('2 ',AR.Estado)
            END) as Estado2")
            ])
            ->join("people as p", function ($join) {
                $join->on("p.id", "AR.Identificacion_Funcionario");
            })
            ->when(request()->get('id'), function ($q, $fill) {
                $q->where("AR.Id_Orden_Compra_Nacional", $fill);
            })
            ->orderBy('Fecha', 'desc')
            ->orderBy('Estado2', 'asc');
        return $this->success($query->get());
    }

    public function detallePerfil()
    {
        $query = Perfil::alias("PE")
            ->select(["PE.*", "PF.*"])
            ->join("Perfil_Funcionario as PF", function ($join) {
                $join->on("PF.Id_Perfil", "PE.Id_Perfil");
            })
            ->whereIn("PE.Id_Perfil", [1, 29, 16, 44]) // 1 Administrador General, 29 es Gerente Compras, 16 Administrador, 44 Grerente Comercial.
            ->when(request()->get('funcionario'), function ($q, $fill) {
                $q->where("PF.Identificacion_Funcionario", $fill);
            });

        $numregQuery = count($query->get()->toArray()); // Si hay registros significa que tiene permisos.
        return $this->success(["status" => ($numregQuery > 0)]);
    }

    public function detalleRechazo()
    {
        return $this->success(DB::table("Tipo_Rechazo")->get());
    }

    public function detallePreCompra($id)
    {
        $encabezado = DB::table("Pre_Compra", "PC")
            ->select(
                "PC.*",
                "pr.nit",
                DB::raw("ifnull(pr.social_reason,concat(
                pr.first_name,
                IF(isnull(pr.second_name) = 0,' ',''),
                ifnull(pr.second_name,''),
                ' ',
                pr.first_surname,
                IF(isnull(pr.second_surname) = 0,' ',''),
                ifnull(pr.second_surname,'')
            )) as NombreProveedor")
            )
            ->joinSub($this->proveedores, "pr", function ($join) {
                $join->on("pr.nit", "PC.Id_Proveedor");
            })
            ->when($id, function ($q, $fill) {
                $q->where('PC.Id_Pre_Compra', $fill);
            });

        $proveedor = DB::query()->select(
            "PR.nit as Id_Proveedor",
            DB::raw("CONCAT(PR.NombreProveedor,' - ',PR.nit) as NombreProveedor")
        )
            ->fromSub($encabezado, "PR");

        $productos = DB::table("Producto_Pre_Compra", "PPC")
            ->join("Producto as p", function ($join) {
                $join->on("p.Id_Producto", "PPC.Id_Producto");
            })
            ->when($id, function ($q, $fill) {
                $q->where('POCN.Id_Pre_Compra', $fill);
            });
        return $this->success(["Datos" => $encabezado->get(), "Proveedor" => $proveedor->get(), "Productos" => $productos->get()]);
    }

    public function preCompras()
    {
        return $this->success(
            PreCompra:: as("PC")
                ->select(
                    "PC.*",
                    "p.image",
                    "pr.social_reason as Nombre ",
                    DB::raw("CONCAT('OP',ifnull(OP.Id_Orden_Pedido,'')) as Orden_Pedido")
                )
                ->join("people as p", function ($join) {
                    $join->on("p.id", "PC.Identificacion_Funcionario");
                })
                ->leftJoin("Orden_Pedido as OP", function ($join) {
                    $join->on('OP.Id_Orden_Pedido', 'PC.Id_Orden_Pedido');
                })
                ->joinSub($this->proveedores, "pr", function ($join) {
                    $join->on("pr.nit", "PC.Id_Proveedor");
                })
                ->when(request('funcionario'), function ($q, $fill) {
                    $q->where("PC.Identificacion_Funcionario", $fill);
                })
                ->whereRaw("PC.Estado='Pendiente'")->orderBy("PC.Id_Pre_Compra", "desc")
                ->get()
        );
    }

    public function getFuncionarios()
    {
        return $this->success(
            Person::select(
                "id as value",
                DB::raw("concat(
                first_name,
              IF(isnull(second_name) = 0,' ',''),
                ifnull(second_name,''),
                ' ',
                first_surname,
              IF(isnull(second_surname) = 0,' ',''),
                ifnull(second_surname,'')
            ) as text")
            )
                ->when(request('depen'), function ($q, $fill) {
                    $q->whereHas('work_contract', function ($q2) use ($fill) {
                        $q2->whereHas('position', function ($q3) use ($fill) {
                            $q3->whereHas('dependency', function ($q4) use ($fill) {
                                $q4->where('dependencies.name', 'like', '%' . $fill . '%');
                            });
                        });
                    });
                })
                ->whereRaw("status = 'Activo'")->get()
        );
    }

    public function actualizarEstadoPreCompra($id)
    {
        return $this->success(
            PreCompra::where('Id_Pre_Compra', request()->get('id_pre_compra'))
                ->update(['Estado' => 'Solicitada'])
        );
    }

    public function storeCompra(Request $request)
    {
        try {
            $data = $request->except('Productos', 'PurchaseRequestIds');
            $data['Codigo'] = generateConsecutive('Orden_Compra_Nacional');
            $productos = $request->Productos;
            $purchaseRequestIds = $request->PurchaseRequestIds;

            $ocn = OrdenCompraNacional::updateOrCreate(
                ['Id_Orden_Compra_Nacional' => $data['Id_Orden_Compra_Nacional']],
                $data
            );

            $ordenNueva = $ocn->wasRecentlyCreated;

            foreach ($productos as $producto) {
                $producto['Id_Orden_Compra_Nacional'] = $ocn->Id_Orden_Compra_Nacional;
                ProductoOrdenCompraNacional::updateOrCreate(
                    ['Id_Producto_Orden_Compra_Nacional' => $producto['Id_Producto_Orden_Compra_Nacional']],
                    $producto
                );
            }

            ActividadOrdenCompra::create(
                [
                    'Id_Orden_Compra_Nacional' => $ocn->Id_Orden_Compra_Nacional,
                    'Identificacion_Funcionario' => $ocn->Identificacion_Funcionario,
                    'Detalles' => "Se " . (($ordenNueva) ? 'creó' : 'editó') . " la orden de compra con código " . $ocn->Codigo,
                    'Fecha' => date("Y-m-d H:i:s"),
                    'Estado' => ($ordenNueva) ? 'Creacion' : 'Edicion'
                ]
            );

            // Relacionar la orden de compra con las solicitudes de compra
            if (!empty($purchaseRequestIds)) {
                foreach ($purchaseRequestIds as $purchaseRequestId) {
                    $ocn->purchaseRequests()->attach($purchaseRequestId, ['status' => 'activo']);
                }
            }

            sumConsecutive('Orden_Compra_Nacional');

            return $this->success('Orden de compra ' . (($ordenNueva) ? 'creada' : 'actualizada') . ' con éxito');
        } catch (\Throwable $th) {
            return $this->errorResponse(["file" => $th->getFile() . ":" . $th->getLine(), "err" => $th->getCode(), "data" => $th->getMessage()]);
        }
    }


    /* if (request()->get('id_pre_compra')) {
        DB::table("Pre_Compra")->updateOrInsert(
            ['Id_Pre_Compra' => request()->get('id_pre_compra')],
            ['Id_Orden_Compra_Nacional' => $result->Id_Orden_Compra_Nacional]
        );
    } */

    public function getEstadosCompra()
    {
        $array = [
            "Pendiente",
            "Anulada",
            "Recibida"
        ];
        return $this->success($array);
    }

    public function setEstadoCompra(Request $request)
    {
        try {
            $ordenCompra = OrdenCompraNacional::find($request->id);

            if ($request->estado == 'Anulada' || $request->estado == 'Pendiente') {
                $ordenCompra->update(['Estado' => $request->estado]);
            } else {
                $ordenCompra->update(['Aprobacion' => $request->estado]);
            }

            if ($request->estado == 'Rechazada' || $request->estado == 'Anulada') {
                // Buscar todas las solicitudes de compra relacionadas en la tabla pivote
                $purchaseRequests = $ordenCompra->purchaseRequests()->get();
                foreach ($purchaseRequests as $purchaseRequest) {
                    // Actualizar el estado a 'inactivo' en la tabla pivote
                    $ordenCompra->purchaseRequests()
                        ->updateExistingPivot($purchaseRequest->pivot->id_purchase_request, ['status' => 'inactivo']);
                }
            }

            $motivo = "";

            if (in_array($request->estado, ['Aprobada', 'Rechazada'])) {
                if ($request->motivo) {
                    $motivo = " con el siguiente motivo: " . TipoRechazo::find($request->motivo)->Nombre;
                }
                ActividadOrdenCompra::create(
                    [
                        'Id_Orden_Compra_Nacional' => $request->id,
                        'Identificacion_Funcionario' => auth()->user()->person_id,
                        'Detalles' => 'Ha sido ' . strtolower($request->estado) . $motivo . '.',
                        'Fecha' => Carbon::now(),
                        'Estado' => ($request->estado == "Aprobada") ? 'Aprobacion' : 'Rechazada'
                    ]
                );
            }

            return $this->success([
                "mensaje" => 'Orden de compra ' . strtolower($request->estado) . $motivo . '.',
                "tipo" => "success",
                "titulo" => "Operación exitosa"
            ]);
        } catch (\Throwable $th) {
            return $this->errorResponse([
                "file" => $th->getFile() . ":" . $th->getLine(),
                "err" => $th->getCode(),
                "data" => [
                    "mensaje" => $th->getMessage(),
                    "tipo" => "error",
                    "titulo" => "Error en la operación"
                ]
            ]);
        }
    }

    private function getCompany()
    {
        return Person::find(Auth()->user()->person_id)->company_worked_id;
    }

    public function descargar($id)
    {
        $company = Company::find($this->getCompany());
        $image = $company->page_heading;
        $data = OrdenCompraNacional::with('products', 'person', 'third', 'store', 'activity')
            ->find($id);
        $datosCabecera = (object) array(
            'Titulo' => 'Orden de compra',
            'Codigo' => $data->Codigo,
            'Fecha' => $data->created_at,
            'CodigoFormato' => $data->format_code
        );
        $pdf = PDF::loadView(
            'pdf.orden_compra',
            compact('data', 'company', 'datosCabecera', 'image')
        );
        return $pdf->download('orden_compra.pdf');
    }

}
