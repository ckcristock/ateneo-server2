<?php

namespace App\Http\Controllers;

use App\Models\ThirdParty;
use App\Models\ThirdPartyField;
use App\Models\ThirdPartyPerson;
use App\Http\Services\HttpResponse;
use App\Http\Services\QueryBaseDatos;
use App\Http\Services\consulta;
use App\Models\CompensationFund;
use App\Models\People;
use App\Models\Person;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rules\Exists;

class ThirdPartyController extends Controller
{
    use ApiResponser;

    private function getCompany()
    {
        return Person::find(Auth()->user()->person_id)->company_worked_id;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {

        $response = ThirdParty::with('municipality')
            ->when(Request()->get('nit'), function ($q, $fill) {
                $q->where('nit', 'like', '%' . $fill . '%');
            })
            ->when($request->name, function ($q, $fill) {
                $q->where(DB::raw('IFNULL(social_reason, CONCAT_WS(" ", first_name, first_surname))'), 'like', '%' . $fill . '%');
                /* $q->where('social_reason', 'like', '%' . $fill . '%'); */
            })
            ->when($request->third_party_type, function ($q, $fill) {
                if ($fill == 'Todos') {
                    return null;
                } else if ($fill == 'Cliente') {
                    $q->where('is_client', 1);
                } else if ($fill == 'Proveedor') {
                    $q->where('is_supplier', 1);
                }
            })
            ->when(Request()->get('email'), function ($q, $fill) {
                $q->where('email', 'like', '%' . $fill . '%');
            })
            ->when(Request()->get('cod_dian_address'), function ($q, $fill) {
                $q->where('cod_dian_address', 'like', '%' . $fill . '%');
            })
            ->when(Request()->get('phone'), function ($q, $fill) {
                $q->where(function ($sq) use ($fill) {
                    $sq->where('landline', 'like', '%' . $fill . '%')
                        ->orwhere('cell_phone', 'like', '%' . $fill . '%');
                });
            })
            ->when(Request()->get('municipio'), function ($q, $fill) {
                $q->whereHas('municipality', function ($q) {
                    $q->where('name', 'like', '%' . \Request()->get('municipio') . '%');
                });
            })
            ->where('company_id', $this->getCompany())
            ->select("*", DB::raw('IFNULL(social_reason, CONCAT_WS(" ", first_name, first_surname)) as name'))
            ->orderBy('state', 'asc')
            ->orderBy('name', 'asc')
            ->paginate(request()->get('pageSize', 10), ['*'], 'page', request()->get('page', 1));
        return $this->success(
            $response
        );
    }

    public function thirdParties()
    {
        return $this->success(
            ThirdParty::select(
                DB::raw('UPPER(IFNULL(social_reason, CONCAT_WS(" ", first_name, first_surname))) as text'),
                'id as value',
                'company_id'
            )
                ->when(Request()->get('name'), function ($q, $fill) {
                    $q->where(DB::raw('concat(IFNULL(social_reason, " "), IFNULL(first_name,"")," ",IFNULL(first_surname,"") )'), 'like', '%' . $fill . '%');
                })
                ->where('state', 'Activo')
                /* ->when(Request()->get('get_full'), function ($q, $fill) {
                    $q->orWhere('state', 'Inactivo');
                }) */
                ->where('company_id', $this->getCompany())
                ->orderBy('text')
                ->get()
        );
    }

    public function thirdPartyProvider()
    {
        return $this->success(
            ThirdParty::select(DB::raw('id as value'))
                ->name("text")
                ->when(Request()->get('name'), function ($q, $fill) {
                    $q->where(DB::raw('concat(IFNULL(social_reason, " "), IFNULL(first_name,"")," ",IFNULL(first_surname,"") )'), 'like', '%' . $fill . '%');
                })
                ->where('state', 'Activo')
                ->where('is_supplier', 1)
                ->where('company_id', $this->getCompany())
                ->get()
        );
    }

    public function thirdPartyClient()
    {
        return $this->success(
            ThirdParty::select(
                DB::raw('IFNULL(social_reason,concat(first_name," ",first_surname)) as text'),
                'id as value',
                'nit'
            )->when(Request()->get('name'), function ($q, $fill) {
                $q->where(DB::raw('concat(IFNULL(social_reason, " "), IFNULL(first_name,"")," ",IFNULL(first_surname,"") )'), 'like', '%' . $fill . '%');
            })
                ->where('state', 'Activo')
                ->where('is_client', 1)
                ->orderBy('text')
                ->get()
        );
    }

    public function getFields()
    {
        return $this->success(
            ThirdPartyField::where('state', '=', 'activo')
                ->where('company_id', $this->getCompany())
                ->get()
        );
    }

    public function filtrarTerceros(Request $request)
    {
        $coincidencia = $request->input('coincidencia', '');

        $query = Person::selectRaw('id AS Nit, id AS Id, CONCAT(id, " - ", CONCAT_WS(" ", first_name, first_surname)) AS Nombre_Tercero, CONCAT(id, " - ", CONCAT_WS(" ", first_name, first_surname)) AS Nombre, "Funcionario" as Tipo')
            ->where('id', 'LIKE', '%' . $coincidencia . '%')
            ->orWhere('first_name', 'LIKE', '%' . $coincidencia . '%')
            ->orWhere('first_surname', 'LIKE', '%' . $coincidencia . '%');

        $query->union(
            ThirdParty::selectRaw('id AS Nit, id AS Id, CONCAT(id, " - ", CONCAT_WS(" ", first_name, first_surname)) AS Nombre_Tercero, CONCAT(id, " - ", CONCAT_WS(" ", first_name, first_surname)) AS Nombre, "Cliente" as Tipo')
                ->where('is_client', 1)
                ->where('id', 'LIKE', '%' . $coincidencia . '%')
                ->orWhere('first_name', 'LIKE', '%' . $coincidencia . '%')
                ->orWhere('first_surname', 'LIKE', '%' . $coincidencia . '%')
        );

        $query->union(
            ThirdParty::selectRaw('id AS Nit, id AS Id, CONCAT(id, " - ", IF((first_name IS NULL OR first_name = ""), first_name, CONCAT_WS(" ", first_name, second_name, first_surname, second_surname))) AS Nombre_Tercero, CONCAT(id, " - ", IF((first_name IS NULL OR first_name = ""), first_name, CONCAT_WS(" ", first_name, second_name, first_surname, second_surname))) AS Nombre, "Proveedor" as Tipo')
                ->where('is_supplier', 1)
                ->where('id', 'LIKE', '%' . $coincidencia . '%')
                ->orWhere('first_name', 'LIKE', '%' . $coincidencia . '%')
                ->orWhere('first_surname', 'LIKE', '%' . $coincidencia . '%')
        );

        $matches = $query->get();

        return response()->json($matches);
    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $data = $request->except(["person"]);
        if ($data['image']) {
            $typeImage = '.' . $request->typeImage;
            $data["image"] = URL::to('/') . '/api/image?path=' . saveBase64($data["image"], 'third_parties/', true);
        }
        if ($data['rut']) {
            $typeRut = '.' . $request->typeRut;
            $base64 = saveBase64File($data["rut"], 'thirdPartiesRut/', false, $typeRut);
            $data["rut"] = URL::to('/') . '/api/file?path=' . $base64;
        }
        $people = request()->get('person');
        try {
            if (in_array('Cliente', $data['third_party_type'])) {
                $data['is_client'] = true;
            }
            if (in_array('Proveedor', $data['third_party_type'])) {
                $data['is_supplier'] = true;
            }
            $data['company_id'] = $this->getCompany();
            $thirdParty = ThirdParty::create($data);
            if (count($people) > 0) {
                foreach ($people as $person) {
                    $person["third_party_id"] = $thirdParty->id;
                    $person["company_id"] = $this->getCompany();
                    ThirdPartyPerson::create($person);
                }
            }
            return $this->success('Guardado con éxito');
        } catch (\Throwable $th) {
            return $this->errorResponse([$th->getMessage(), $th->getLine(), $th->getFile()], 500);
        }
    }

    public function changeState(Request $request)
    {
        try {
            $third = ThirdParty::find(request()->get('id'));
            $third->update($request->all());
            return $this->success('Proceso Correcto');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 500);
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
        $third_party_query = ThirdParty::with('country', 'document_type_', 'municipality', 'department')
            ->name()
            ->find($id);
        $third_party = ThirdParty::find($id);
        $third_party_fields = ThirdPartyField::where('company_id', $this->getCompany())->get();
        $people = $third_party->thirdPartyPerson()->paginate(Request()->get('pageSizePeople', 10), ['*'], 'pagePeople', Request()->get('pagePeople', 1));
        return $this->success(
            [
                "third_party_query" => $third_party_query,
                "people" => $people,
                "third_party_fields" => $third_party_fields
            ]
        );
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit($id)
    {
        return $this->success(
            ThirdParty::with('thirdPartyPerson')
                ->find($id)
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $validator = ThirdParty::find($id);
        $data = $request->except(["person"]);
        if ($data["image"] != $validator["image"]) {
            $typeImage = '.' . $request->typeImage;
            $data["image"] = URL::to('/') . '/api/image?path=' . saveBase64($data["image"], 'third_parties/', true);
        }
        if ($data["rut"] != $validator["rut"]) {
            $typeRut = '.' . $request->typeRut;
            $base64 = saveBase64File($data["rut"], 'thirdPartiesRut/', false, $typeRut);
            $data["rut"] = URL::to('/') . '/api/file?path=' . $base64;
        }
        $people = request()->get('person');
        try {
            if (in_array('Cliente', $data['third_party_type'])) {
                $data['is_client'] = true;
            } else {
                $data['is_client'] = false;
            }
            if (in_array('Proveedor', $data['third_party_type'])) {
                $data['is_supplier'] = true;
            } else {
                $data['is_supplier'] = false;
            }
            $thirdParty = ThirdParty::find($id)
                ->update($data);
            foreach ($people as $person) {
                if (isset($person["id"])) {
                    $thirdPerson = ThirdPartyPerson::find($person["id"]);
                    $thirdPerson->update($person);
                } else {
                    $person["third_party_id"] = $id;
                    ThirdPartyPerson::create($person);
                }
            }
            return $this->success('Actualizado con éxito');
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage(), 500);
        }
    }



    public function buscarProveedor()
    {
        $proveedores = ThirdParty::select('id as Id_Proveedor')
            ->selectRaw('IFNULL(social_reason, CONCAT_WS(" ", first_name, first_surname, second_name, second_surname)) as NombreProveedor')
            ->where('is_client', 1)
            ->get();

        return response()->json($proveedores);
    }


    public function nitBuscar()
    {
        /* $third = ThirdParty::where('state', 'Activo')
            ->select(
                'id',
                DB::raw('IFNULL(social_reason, CONCAT_WS(" ", first_name, first_surname)) as Nombre'),
                DB::raw('"Cliente" AS Tipo')
            )->get();
        $people = Person::where('status', 'Activo')
            ->select(
                'id',
                DB::raw('CONCAT(id, " - ", first_name," ", first_surname) AS Nombre'),
                DB::raw('"Funcionario" AS Tipo')
            )->get();
        $compensation_funds = CompensationFund::where('status', 'Activo')
            ->select(
                'nit as id',
                DB::raw('CONCAT_WS(" ", nit, name) AS Nombre'),
                DB::raw('"Caja_Compensacion" AS Tipo')
            )->get(); */

        $query = ThirdParty::where('state', 'Activo')
            ->select(
                'id',
                DB::raw('IFNULL(social_reason COLLATE utf8mb4_spanish_ci, CONCAT_WS(" ", first_name, first_surname)) as Nombre'),
                DB::raw('"Cliente" AS Tipo')
            )
            ->union(
                Person::where('status', 'Activo')
                    ->select(
                        'id',
                        DB::raw('CONCAT(id, " - ", first_name," ", first_surname) AS Nombre'),
                        DB::raw('"Funcionario" AS Tipo')
                    )
            )
            ->union(
                CompensationFund::where('status', 'Activo')
                    ->select(
                        'nit as id',
                        DB::raw('CONCAT_WS(" ", nit, name) AS Nombre'),
                        DB::raw('"Caja_Compensacion" AS Tipo')
                    )
            )
            ->get();


        return response()->json($query);
    }

    public function porTipo()
    {
        $tipo = isset($_REQUEST['Tipo']) ? $_REQUEST['Tipo'] : false;

        if ($tipo) {
            $query = $this->query($tipo);

            $oCon = new consulta();
            $oCon->setQuery($query);
            $oCon->setTipo('Multiple');
            $clientes = $oCon->getData();

            $res = $clientes;
            return response()->json($res);
        }
    }

    public function listaCliente()
    {
        $clientesThirdParty = ThirdParty::query()
            ->activeClients()
            ->withFullNameOrSocialReason();
        $clientesPeople = Person::query()
            ->activeWithContractInCompany()
            ->withFullName();
        $clientes = $clientesThirdParty/* ->union($clientesPeople) */
            ->orderBy('Nombre')
            ->get();
        return response()->json($clientes);
    }


    function query($tipo)
    {
        if ($tipo == 'Funcionario') {
            $select = 'SELECT id AS Id_Cliente,
                         CONCAT(id, " - ",first_name," ",first_surname) AS Nombre
                         FROM people';
        } else if ($tipo == 'Cliente') {
            $select = 'SELECT id as Id_Cliente, IFNULL(social_reason, CONCAT_WS(" ", first_name, first_surname)) AS Nombre
            FROM third_parties WHERE is_client = 1';
        } else if ($tipo == 'Proveedor') {
            $select = 'SELECT id AS Id_Cliente ,
                        IFNULL(social_reason, CONCAT_WS(" ", first_name, first_surname)) AS Nombre FROM third_parties WHERE is_supplier = 1 ';
        }
        return $select;
    }

    public function listaProveedores()
    {
        $proveedores = ThirdParty::select('id AS Id_Proveedor', DB::raw('CONCAT(CONCAT_WS(" ", first_name, first_surname), " - ", id) AS Nombre'))
            ->where('is_supplier', 1)
            ->union(People::select('id AS Id_Proveedor', DB::raw('CONCAT(CONCAT_WS(" ", first_name, first_surname), " - ", id) AS Nombre')))
            ->get();

        return response()->json($proveedores);
    }

    public function listaProductosVencer()
    {
        $condicion = [];
        if (request()->filled('nom')) {
            $condicion[] = ['P.Nombre_Comercial', 'LIKE', '%' . request('nom') . '%'];
        }
        if (request()->filled('lot')) {
            $condicion[] = ['INN.Lote', 'LIKE', '%' . request('lot') . '%'];
        }
        if (request()->filled('pro')) {
            $condicion[] = ['PR.Nombre', 'LIKE', '%' . request('pro') . '%'];
        }

        $datos['Lista'] = ThirdParty::selectRaw('IF(is_supplier, 1, 0) as id')
            ->join('Orden_Compra_Nacional as OCN', 'third_parties.id', '=', 'OCN.Id_Proveedor')
            ->join('Acta_Recepcion as AR', function ($join) {
                $join->on('OCN.Id_Orden_Compra_Nacional', '=', 'AR.Id_Orden_Compra_Nacional')
                    ->on('OCN.Id_Bodega_Nuevo', '=', 'AR.Id_Bodega_Nuevo');
            })
            ->join('Producto_Acta_Recepcion as PAC', 'PAC.Id_Acta_Recepcion', '=', 'AR.Id_Acta_Recepcion')
            ->join('Inventario_Nuevo as INN', function ($join) {
                $join->on('PAC.Lote', '=', 'INN.Lote')
                    ->on('PAC.Id_Producto', '=', 'INN.Id_Producto');
            })
            ->join('Producto as P', 'INN.Id_Producto', '=', 'P.Id_Producto')
            ->where('INN.Cantidad', '>', 0)
            ->where(function ($query) use ($condicion) {
                foreach ($condicion as $cond) {
                    $query->where($cond[0], $cond[1], $cond[2]);
                }
            })
            ->groupBy('P.Nombre_Comercial', 'INN.Lote', 'INN.Fecha_Vencimiento', 'third_parties.first_name', 'INN.Id_Producto', DB::raw('IF(third_parties.is_supplier, 1, 0)'))
            ->orderBy('FechaEntrega', 'ASC')
            ->orderBy('INN.Fecha_Vencimiento', 'ASC')
            ->selectRaw('P.Nombre_Comercial as NomProducto, INN.Lote, SUM(INN.Cantidad) as Cantidades, DATE_ADD(INN.Fecha_Vencimiento, INTERVAL 1 MONTH) as FechaEntrega, INN.Fecha_Vencimiento, CURDATE() as Fecha, third_parties.first_name as NomProveedor, INN.Id_Producto, IF(third_parties.is_supplier, 1, 0)')
            ->paginate(request()->get('pageSize', 10), ['*'], 'page', request()->get('page', 1));

        return $this->success($datos);
    }


}
