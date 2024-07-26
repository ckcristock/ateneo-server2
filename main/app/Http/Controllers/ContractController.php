<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Policy;
use App\Models\Service;
use App\Models\TechnicNote;
use App\Traits\ApiResponser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContractController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $contract = Contract::query();

        $contract->select(
            'id',
            'id AS value',
            'company_id',
            DB::raw("Concat_ws(' ', code, '-' , name) As text")
        );

        $contract->when(request()->get('eps_id'), function (Builder $q) {
            $q->where(function (Builder $q) {
                $q->where('administrator_id', request()->get('eps_id'))
                    /* ->orWhere('regimen_id', request()->get('regimen_id')) */;
            });
        });

        $contract->when(request()->get('company_id'), function (Builder $q) {
            $q->where('company_id', request()->get('company_id'));
        });

        $contract->when(request()->get('municipalities_id'), function (Builder $q) {
            $q->whereHas('municipalities', function ($query) {
                $query->select('municipalities.id');
                return $query->where('municipalities.id', '=', request()->get('municipalities_id'));
            });
        });

        $contract->when(request()->get('department_id'), function (Builder $q) {
            $q->whereHas('departments_', function ($query) {
                $query->select('departments.id');
                return $query->where('departments.id', '=', request()->get('department_id'));
            });
        });

        $contract->when(request()->get('regimen_id'), function (Builder $q) {
            $q->whereHas('regimentypes', function ($query) {
                $query->select('regimen_types.id');
                return $query->where('regimen_types.id', '=', request()->get('regimen_id'));
            });
        });

        $contract->when(request()->get('type_service'), function (Builder $q) {
            $q->whereHas('type_service', function ($query) {
                $query->select('type_services.id');
                return $query->where('type_services.id', '=', request()->get('type_service'));
            });
        });
        $result = $contract->where('state', '=', 'Activo');
        $result = $result->get();
        return $this->success($result);
    }


    public function paginate()
    {
        return $this->success(Contract::with(['company:id,name', 'administrator:id,name', 'regimentypes:id,name'])
            ->when(request()->get('name'), function ($q, $fill) {
                $q->where('name', 'like', '%' . $fill . '%');
            })
            ->when(request()->get('code'), function ($q, $fill) {
                $q->where('code', 'like', '%' . $fill . '%');
            })
            ->orderBy('state')
            ->paginate(request()->get('pageSize', 10), ['*'], 'page', request()->get('page', 1)));
        /* $result = $contract->get(['id', 'company_id', 'administrator_id', 'start_date', 'end_date', 'name', 'code', 'status']);
        return $this->success($result); */
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            if ($request->has('state') && $request->has('status')) {
                Contract::where('id', $request->id)
                    ->update(['state' => $request->state, 'status' => $request->status]);
                return $this->success('Actualizado con éxito');
            }
            $contract = Contract::updateOrCreate(
                ["id" => request()->get("id")],
                [
                    "name" => request()->get("name"),
                    "code" => request()->get("code"),
                    "number" => request()->get("number"),
                    "administrator_id" => request()->get("administrator_id"),
                    "start_date" => request()->get("start_date"),
                    "end_date" => request()->get("end_date"),
                    "price" => request()->get("price"),
                    "company_id" => request()->get("company_id"),
                    "payment_method_id" => request()->get("payment_method_id"),
                    "payment_methods_contracts_id" => request()->get("payment_methods_contracts_id"),
                    "benefits_plans_id" => request()->get("benefits_plans_id")
                ]
            );

            $departments = request()->get("department_id");
            if ($departments[0] == 0)
                $departments = DB::table('departments')->pluck('id')->toArray();

            $municipalities = request()->get("municipality_id");
            if ($municipalities[0] == 0) {
                $municipalities = DB::table('municipalities')->whereIn('department_id', $departments)->pluck('id')->toArray();
            }

            $regimens = request()->get("regimen_id");
            if ($regimens[0] == 0)
                $regimens = DB::table('regimen_types')->pluck('id')->toArray();

            $type_service = request()->get("type_service_id");
            if ($type_service[0] == 0)
                $type_service = DB::table('type_service')->pluck('id')->toArray();

            $locations = request()->get("location_id");
            if ($locations[0] == 0) {
                $locations = DB::table('locations')->where('company_id', request()->get("company_id"))->pluck('id')->toArray();
            }

            $contract->departments_()->sync($departments);
            $contract->municipalities()->sync($municipalities);
            $contract->regimentypes()->sync($regimens);
            $contract->locations()->sync($locations);
            $contract->type_service()->sync($type_service);
            Policy::where('contract_id', $contract->id)->delete();
            foreach (request()->get("poliza") as $poliza) {
                Policy::create([
                    "contract_id" => $contract->id,
                    "code" => $poliza["codigopoliza"],
                    "start" => $poliza["iniciopoliza"],
                    "end" => $poliza["finpoliza"],
                    "name" => $poliza["nombrepoliza"],
                    "coverage" => $poliza["coberturapoliza"],
                ]);
            }
            $technicNotes = TechnicNote::where('contract_id', $contract->id)->pluck('id');
            Service::whereIn('technic_note_id', $technicNotes->values())->delete();
            TechnicNote::where('contract_id', $contract->id)->delete();
            foreach (request()->get("technicalNote") as $technicalNote) {
                $newTechnicalNote = TechnicNote::create([
                    "contract_id" => $contract->id,
                    "start" => $technicalNote["techn_note_date_init"],
                    "end" => $technicalNote["techn_note_date_end"],
                    "anio" => $technicalNote["techn_note_year_cups"],
                    "is_active" => ($technicalNote["is_default"]) ? 1 : 0,
                ]);
                foreach ($technicalNote["cups"] as $service) {
                    $newService = Service::create([
                        "technic_note_id" => $newTechnicalNote->id,
                        "cup_id" => $service["namec"]["value"],
                        "value" => $service["valor"],
                        "centro_costo_id" => $service["centro_costo_id"],
                        "frequency" => $service["frequency"],
                        "speciality_id" => $service["speciality_id"],
                        "route_id" => $service["route_id"] ?? null,
                    ]);
                }
            }

            return response()->success('Contrato creado correctamente.');
        } catch (\Throwable $th) {
            return $this->errorResponse([$th->getMessage(), $th->getFile(), $th->getLine()]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return response()->json($id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit($id)
    {
        $data2 = Contract::with('departments')->where('id', $id)->get();

        $data = Contract::with([
            /* 'company:id,name', */
            'type_service:id,name',
            'regimentypes:id,name',
            'administrator:id,name',
            'departments',
            'municipalities:id,name',
            'locations:id,name',
            'technicNotes',
            'technicNotes.services',
            'technicNotes.services.specialities' => function ($q) {
                $q->select('specialities.id', 'specialities.name as text');
            },
            'technicNotes.services.cup' => function ($q) {
                $q->select('*', 'id as value', DB::raw("CONCAT(code, ' - ' ,description) as text"));
            },
            'technicNotes.services.cup.specialities' => function ($q) {
                $q->select('specialities.id as value', 'specialities.name as text');
            },
            'policies'
        ])->find($id);
        return $this->success($data);
    }

    public function getPaymentMethodsContracts()
    {
        return $this->success(DB::table('payment_methods_contracts')->get(['id as value', 'name as text']));
    }

    public function getAttentionRoutes()
    {
        return $this->success(DB::table('attention_routes')->get(['id as value', 'name as text']));
    }

    public function getAttentionRoutesCustom(Request $request)
    {
        return $this->success(
            DB::table('attention_routes')
                ->when($request->birthday, function ($q, $fill) use ($request) {
                    $q->where('age_min', '<=', $fill);
                    $q->where('age_max', '>', $fill);
                    $q->where('gender', 'like', '%' . $request->gender . '%');
                })
                ->get(['id as value', 'name as text'])
        );
    }
}
