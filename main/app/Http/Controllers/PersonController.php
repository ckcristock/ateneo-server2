<?php

namespace App\Http\Controllers;

use App\Exports\PeopleExport;
use App\Http\Requests\ProfessionalRequest;
use App\Http\Requests\StorePersonRequest;
use App\Models\Board;
use App\Models\Company;
use App\Models\CompanyPerson;
use App\Models\Dispensing;
use App\Models\Eps;
use App\Models\FixedTurn;
use App\Models\Person;
use App\Models\Usuario;
use App\Models\WorkContract;
use App\Services\CognitiveService;
use App\Traits\ApiResponser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class PersonController extends Controller
{
    use ApiResponser;

    public $ocpApimSubscriptionKey = "df2f7a1cb9a14c66b11a7a2253999da5";
    public $azure_grupo = "personalnuevo";
    public $uriBase = "https://facemaqymon2021.cognitiveservices.azure.com/face/v1.0";

    private function getCompany(): int
    {
        return auth()->user()->person->company_worked_id;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request, $ips = 0, $speciality = 0)
    {
        if ($request->type) {
            $query = Person::where('people_type_id', 2)
                ->where('status', 'activo')
                ->when($request->dependency_id, fn($query) => $query->whereHas('work_contract.position', fn($query) => $query->where('dependency_id', $request->dependency_id)))
                ->orderBy('first_name')
                ->get([
                    "id as value",
                    DB::raw('UPPER(CONCAT_WS(" ", first_name, second_name, first_surname, second_surname)) as text')
                ]);
            return $this->success($query);
        }

        $query = Person::orderBy('first_name')
            ->where('status', 'activo')
            ->whereHas('specialties', fn($query) => $query->where('id', $speciality))
            ->where(
                fn($query) => $query
                    ->when(
                        $request->company_id,
                        fn($query) => $query
                            ->whereHas('restriction', fn($q) => $q->where('company_id', $request->company_id))
                            ->orWhereHas('restriction.companies', fn($q) => $q->where('companies.id', $request->company_id))
                    )
            )
            ->get(['*', 'id As value', DB::raw('UPPER(CONCAT_WS(" ", first_name, second_name, first_surname, second_surname)) as text')]);
        return response()->success($query);
    }

    public function getPersonCompany(Request $request)
    {
        return $this->success(
            Person::with('contractultimate')
                ->whereHas('contractultimate', function ($query) {
                    $query->where('company_id', $this->getCompany());
                })
                ->when($request->position, function ($query, $position) {
                    $query->whereHas('contractultimate.position', function ($subQuery) use ($position) {
                        $subQuery->where('id', $position);
                    });
                })
                ->when($request->dependency_id, function ($query, $dependencyId) {
                    $query->whereHas('contractultimate.position.dependency', function ($subQuery) use ($dependencyId) {
                        $subQuery->where('id', $dependencyId);
                    });
                })
                ->when($request->group_id, function ($query, $groupId) {
                    $query->whereHas('contractultimate.position.dependency.group', function ($subQuery) use ($groupId) {
                        $subQuery->where('id', $groupId);
                    });
                })
                ->where('status', 'activo')
                ->get(['id as value', DB::raw("CONCAT_WS(' ', first_name, second_name, first_surname, second_surname) as text ")])
        );
    }

    public function download()
    {
        $activePeople = Person::where('status', 'activo')
            ->with('contractultimate', 'severance_fund', 'eps', 'arl', 'compensation_fund', 'pension_funds')
            ->fullName()
            ->get();
        return Excel::download(new PeopleExport($activePeople), 'funcionarios.xlsx');
    }

    public function peopleSelects()
    {
        $nameFilter = request()->get('name');

        $people = Person::select(
            'id as value',
            DB::raw('CONCAT_WS(" ", first_name, first_surname) as text')
        )
            ->when($nameFilter, function ($query, $name) {
                $query->where(DB::raw('CONCAT_WS(" ", first_name, first_surname)'), 'like', "%$name%");
            })
            ->limit(100)
            ->get();

        return $this->success($people);
    }

    public function myProfle()
    {
        $id = auth()->user()->person_id;
        $person = Person::fullName()->find($id);
        return $this->success($person);
    }

    public function funcionarioPunto()
    {
        return $this->success(
            DB::table("people", "FP")
                ->select("PD.Id_Punto_Dispensacion", "PD.Nombre")
                ->join("Punto_Dispensacion as PD", function ($join) {
                    $join->on("FP.dispensing_point_id", "PD.Id_Punto_Dispensacion");
                })
                ->when(request()->id, function ($q, $fill) {
                    $q->where("FP.id", $fill);
                })->get()
        );
    }

    public function indexPaginate(Request $request): JsonResponse
    {
        $status = '';
        if ($request->status) {
            $statusOptions = [
                1 => 'activo',
                2 => 'inactivo',
                3 => 'liquidado',
                4 => 'preliquidado',
            ];
            $status = $statusOptions[$request->status] ?? '';
        }

        return $this->success(
            Person::with('contractultimate.company')
                ->when($request->name, function (Builder $q, $fill) {
                    $q->where(function (Builder $query) use ($fill) {
                        $query->where("identifier", "like", "%" . $fill . "%")
                            ->orWhere(DB::raw('CONCAT_WS(" ", first_name, first_surname, second_surname)'), "LIKE", "%" . $fill . "%")
                            ->orWhere(DB::raw('CONCAT_WS(" ", second_name, first_surname, second_surname)'), "LIKE", "%" . $fill . "%")
                            ->orWhere(DB::raw('CONCAT_WS(" ", first_surname, second_surname)'), "LIKE", "%" . $fill . "%")
                            ->orWhere(DB::raw('CONCAT_WS(" ", first_name, second_name, first_surname, second_surname)'), "LIKE", "%" . $fill . "%");
                    });
                })
                ->when(
                    $request->dependency_id,
                    fn(Builder $query, $fill) =>
                    $query->whereHas('contractultimate.position', fn(Builder $q) => $q->where('dependency_id', $fill))
                )
                ->when($request->status, fn(Builder $query) => $query->where("status", $status))
                ->when(
                    $request->company_id,
                    fn(Builder $query, $fill) =>
                    $query->whereHas('contractultimate', fn(Builder $q) => $q->where('company_id', $fill))
                        ->orWhereHas('contractUltimateLiquidated.workContractBT', fn(Builder $q) => $q->where('company_id', $fill))
                )
                ->orderBy('first_name', 'asc')
                ->paginate($request->pageSize ?? 12, ['*'], 'page', $request->page ?? 1)
        );
    }

    public function peoplesWithDni(Request $request)
    {
        $search = $request->input('search');
        $people = Person::select(
            "id as value",
            "identifier",
            DB::raw('CONCAT_WS(" ",first_name, second_name, first_surname, second_surname) as text')
        )
            ->when($search, function ($query, $fill) {
                $query->where('identifier', 'like', "%$fill%")
                    ->orWhere(DB::raw('CONCAT_WS(" ", first_name, second_name, first_surname, second_surname)'), 'like', "%$fill%");
            })
            ->where('status', 'activo')
            ->get();

        return $this->success($people);
    }

    public function validarCedula($documento)
    {
        $user = '';
        $exists = DB::table("people")
            ->where('identifier', $documento)
            ->exists();
        if ($exists) {
            $user = Person::where('identifier', $documento)->fullName()->first();
        }
        return $this->success(['exists' => $exists, 'user' => $user]);
    }

    public function getAll(Request $request)
    {
        # code...
        $data = $request->all();
        return $this->success(
            DB::table("people as p")
                ->select(
                    "p.id",
                    "p.identifier",
                    "p.image",
                    "p.status",
                    "p.full_name",
                    "p.first_surname",
                    "p.first_name",
                    "pos.name as position",
                    "d.name as dependency",
                    "p.id as value",
                    // "p.passport_number",
                    // "p.visa",
                    DB::raw('UPPER(CONCAT_WS(" ", first_name, second_name, first_surname, second_surname)) as text'),
                    "c.name as company",
                    DB::raw("w.id AS work_contract_id"),
                    DB::raw("'Funcionario' AS type")
                )
                ->join("work_contracts as w", function ($join) {
                    $join->on(
                        "p.id",
                        "=",
                        "w.person_id"
                    )->where('w.liquidated', 0);
                })
                ->join("companies as c", "c.id", "=", "w.company_id")
                ->join("positions as pos", "pos.id", "=", "w.position_id")
                ->join("dependencies as d", "d.id", "=", "pos.dependency_id")
                ->where("p.status", "activo")
                ->when($request->get('dependencies'), function ($q, $fill) {
                    $q->where("d.id", $fill);
                })
                ->get()
        );
    }

    public function getAllCompany(Request $request)
    {
        return Person::with([
            'contractultimate.position' => function ($query) {
                $query->select('name as position', 'id', 'dependency_id');
            },
            'contractultimate.position.dependency' => function ($query) {
                $query->select('name as dependency', 'id');
            },
            'contractultimate.company' => function ($query) {
                $query->select('name as company', 'id');
            }
        ])
            ->whereHas('contractultimate', function ($query) {
                $query->where('company_id', $this->getCompany());
            })
            ->where("status", "activo")
            ->when($request->get('dependencies'), function ($q, $fill) {
                $q->whereHas('contractultimate.position.dependency', function ($query) use ($fill) {
                    $query->where('id', $fill);
                });
            })
            ->get([
                "id",
                "identifier",
                "image",
                "status",
                "full_name",
                "first_surname",
                "first_name",
                "id as value",
                "passport_number",
                "visa",
                DB::raw('CONCAT_WS(" ", first_name, second_name, first_surname, second_surname) as text'),
                DB::raw("'Funcionario' AS type")
            ]);
    }

    public function basicData($id)
    {
        return $this->success(
            DB::table("people as p")
                ->select(
                    "p.first_name",
                    "p.first_surname",
                    "p.id",
                    "p.identifier",
                    "p.image",
                    "p.second_name",
                    "p.second_surname",
                    "w.salary",
                    "w.id as work_contract_id",
                    "p.signature",
                    "p.title",
                    "p.status"
                )
                ->leftJoin("work_contracts as w", function ($join) {
                    $join->on(
                        "p.id",
                        "=",
                        "w.person_id"
                    )->where('w.liquidated', 0);
                })
                ->where("p.id", "=", $id)
                ->first()
        );
    }

    public function basicDataForm($id)
    {
        return $this->success(
            DB::table("people as p")
                ->select(
                    "p.first_name",
                    "p.first_surname",
                    "p.second_name",
                    "p.second_surname",
                    "p.identifier",
                    "p.image",
                    "p.email",
                    "p.degree",
                    "p.birth_date",
                    "p.gener",
                    "p.marital_status",
                    "p.address",
                    "p.cell_phone",
                    "p.first_name",
                    "p.first_surname",
                    "p.id",
                    "p.image",
                    "p.second_name",
                    "p.second_surname",
                    "p.status",
                    "p.visa",
                    "p.passport_number",
                    "p.title",
                    "p.address",
                    "p.signature",
                )
                ->join("work_contracts as w", function ($join) {
                    $join->on(
                        "p.id",
                        "=",
                        "w.person_id"
                    ) /* ->where('w.liquidated', 0) */ ;
                })
                ->where("p.id", "=", $id)
                ->first()
        );
    }

    public function salaryHistory($id)
    {
        return $this->success(
            WorkContract::where('person_id', $id)
                ->where('liquidated', 1)
                ->with('work_contract_type', 'position')
                ->orderBy('date_end')
                ->get()
        );
    }

    public function updateSalaryInfo(Request $request)
    {
        try {
            $salary = WorkContract::find($request->get('id'));
            $salary->update($request->all());
            $salary->save();
            return response()->json(['message' => 'Se ha actualizado con éxito']);
        } catch (Throwable $th) {
            return $this->error($th->getMessage(), 500);
        }
    }

    public function afiliation($id)
    {
        try {
            return $this->success(
                DB::table("people as p")
                    ->select(
                        "p.eps_id",
                        "e.name as eps_name",
                        "p.compensation_fund_id",
                        "c.name as compensation_fund_name",
                        "p.severance_fund_id",
                        "s.name as severance_fund_name",
                        "p.pension_fund_id",
                        "pf.name as pension_fund_name",
                        "a.id as arl_id",
                        "a.name as arl_name"
                    )
                    ->leftJoin("eps as e", function ($join) {
                        $join->on("e.id", "=", "p.eps_id");
                    })
                    ->leftJoin("arl as a", function ($join) {
                        $join->on("a.id", "=", "p.arl_id");
                    })
                    ->leftJoin("compensation_funds as c", function ($join) {
                        $join->on("c.id", "=", "p.compensation_fund_id");
                    })
                    ->leftJoin("severance_funds as s", function ($join) {
                        $join->on("s.id", "=", "p.severance_fund_id");
                    })
                    ->leftJoin("pension_funds as pf", function ($join) {
                        $join->on("pf.id", "=", "p.pension_fund_id");
                    })
                    ->where("p.id", "=", $id)
                    ->first()
                /* ->get() */
            );
        } catch (Throwable $th) {
            return $this->error($th->getMessage(), 500);
        }
    }

    public function updateAfiliation(Request $request, $id)
    {
        try {
            $afiliation = Person::find($id);
            $afiliation->update($request->all());
            return response()->json([
                "message" => "Se ha actualizado con éxito",
            ]);
        } catch (Throwable $th) {
            return $this->error($th->getMessage(), 500);
        }
    }

    public function fixedTurn()
    {
        try {
            return $this->success(
                FixedTurn::all(['id as value', 'name as text'])
            );
        } catch (Throwable $th) {
            return $this->error($th->getMessage(), 500);
        }
    }

    public function liquidateOrActivate(Request $request, $id)
    {
        try {
            $person = Person::find($id);
            $person->status = $request->status;
            $person->saveOrFail();
            return $this->success($person);
        } catch (Throwable $th) {
            return $this->error($th->getMessage(), 500);
        }
    }


    public function epss()
    {
        try {
            return $this->success(EPS::all(['name as text', 'id as value']));
        } catch (Throwable $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function updateBasicData(Request $request, $id)
    {
        try {
            $person = Person::find($id);
            $personData = $request->all();
            $cognitive = new CognitiveService();
            if (!$person->personId) {
                $person->personId = $cognitive->createPerson($person);
                $person->save();
                $cognitive->deleteFace($person);
            }
            if ($request->signature != $person->signature) {
                $personData['signature'] = URL::to('/') . '/api/image?path=' . saveBase64($personData['signature'], 'people/');
            }
            if ($request->image != $person->image) {
                $personData["image"] = URL::to('/') . '/api/image?path=' . saveBase64($personData["image"], 'people/');
                $person->update($personData);
                $cognitive->deleteFace($person);
                $person->persistedFaceId = $cognitive->createFacePoints($person); //esta línea está dando un error
                $person->save();
                $cognitive->train();
            } else {
                $person->update($personData);
            }

            return response()->json($person);
        } catch (Throwable $th) {
            return $this->error($th->getMessage(), 500);
        }
    }

    public function updateFilePermission(Request $request)
    {
        $person = Person::where('id', $request->id)->first();
        $person->update(['folder_id' => $request->folder_id]);
        return $this->success('Actualizado con éxito');
    }

    public function getFilePermission($id)
    {
        $person = Person::where('id', $id)->pluck('folder_id')->first();
        return $this->success($person);
    }



    public function store(StorePersonRequest $request)
    {
        $per = [];
        try {
            $personData = $request->get("person");

            $workContract = $personData['workContract'];

            $personData["sex"] = $personData['gener'];
            $personData["people_type_id"] = 2;
            $personData["company_id"] = $workContract['company_id'];
            $personData["company_worked_id"] = $workContract['company_id'];

            $full_name_parts = [
                $personData["first_name"],
                $personData["second_name"],
                $personData["first_surname"],
                $personData["second_surname"]
            ];
            $full_name = trim(implode(" ", array_filter($full_name_parts, function ($value) {
                return trim($value) !== '';
            })));

            $personData["full_name"] = $full_name;

            $personData["image"] = URL::to('/') . '/api/image?path=' . saveBase64($personData["image"], realpath('../../../public/app/public/people/'));
            if ($personData["signature"]) {
                $personData['signature'] = URL::to('/') . '/api/image?path=' . saveBase64($personData['signature'], 'signature/');
            }
            $personData["personId"] = null;
            $per = $person = Person::create($personData);
            $contractData = $personData["workContract"];
            $contractData["person_id"] = $person->id;
            WorkContract::create($contractData);
            Usuario::create([
                "person_id" => $person->id,
                "usuario" => $person->identifier,
                "password" => Hash::make($person->identifier),
                "change_password" => 1,
            ]);
            //crear personID
            $cognitive = new CognitiveService();
            $person->personId = $cognitive->createPerson($person);
            if ($personData["image"]) {
                $cognitive->deleteFace($person);
                $person->persistedFaceId = $cognitive->createFacePoints(
                    $person
                );
            }
            $person->save();
            $cognitive->train();

            return $this->success(["id" => $person->id, 'faceCreated' => true]);
        } catch (Throwable $th) {
            if ($per) {
                return $this->success(["id" => $person->id, 'faceCreated' => false]);
            }
            return $this->error($th->getMessage(), 500);
        }
    }

    public function train()
    {
        /*try {
            $response = Http::accept('application/json')->withHeaders([

                'Ocp-Apim-Subscription-Key' => $this->ocpApimSubscriptionKey,
            ])->post($this->uriBase . '/persongroups/' . $this->azure_grupo . '/train', [

            ]);
            $resp = $response->json();
            return response()->json($resp);
        } catch (HttpException $ex) {
            echo $ex;
        }
        */
        $people = Person::whereNotNull("image")
            //->whereNull('personId')
            //->whereNull('persistedFaceId')
            ->orderBy("id", "Desc")
            //->limit(1)
            ->get();
        //dd($people);
        $x = 0;
        foreach ($people as $person) {
            dd((array) $person);
            $x++;
            if ($x == 7) {
                sleep(15);
                $x = 0;
            }
            // $atributos['image'] = $fully;
            /** SI LA PERSONA NO TIENE PERSON ID SE CREA EL REGISTRO EN MICROSOFT */
            if (
                $person->personId == "0" ||
                $person->personId == "null" ||
                $person->personId == null
            ) {
                try {
                    /****** */
                    $parameters = [];
                    $response = Http::accept("application/json")
                        ->withHeaders([
                            "Content-Type" => "application/json",
                            "Ocp-Apim-Subscription-Key" =>
                                $this->ocpApimSubscriptionKey,
                        ])
                        ->post(
                            $this->uriBase .
                            "/persongroups/" .
                            $this->azure_grupo .
                            "/persons" .
                            http_build_query($parameters),
                            [
                                "name" =>
                                    $person->first_name .
                                    " " .
                                    $person->first_surname,
                                "userData" => $person->identifier,
                            ]
                        );
                    $res = $response->json();
                    if (!isset($res["personId"])) {
                        return response()->json($res);
                    }
                    $person->personId = $res["personId"];
                } catch (HttpException $ex) {
                    echo "error: " . $ex;
                }
            }

            if ($person->image) {
                /** VALIDA SI YA TIENE UN ROSTO LO ELIMINA PARA PODER CREAR EL NUEVO */
                if (
                    isset($person->persistedFaceId) &&
                    $person->persistedFaceId != "0"
                ) {
                    $parameters = [];
                    $response = Http::accept("application/json")
                        ->withHeaders([
                            "Content-Type" => "application/json",
                            "Ocp-Apim-Subscription-Key" =>
                                $this->ocpApimSubscriptionKey,
                        ])
                        ->post(
                            $this->uriBase .
                            "/persongroups/" .
                            $this->azure_grupo .
                            "/persons/" .
                            $person->personId .
                            "/persistedFaces/" .
                            $person->persistedFaceId .
                            http_build_query($parameters),
                            [
                                "Ocp-Apim-Subscription-Key" =>
                                    $this->ocpApimSubscriptionKey,
                            ]
                        );
                    $res = $response->json();
                }

                // CREA LOS PUNTOS FACIALES PROPIOS DEL FUNCIONARIO
                $ruta_guardada = $person->image;
                try {
                    $parameters = [
                        "detectionModel" => "detection_02",
                    ];
                    $response = Http::accept("application/json")
                        ->withHeaders([
                            "Content-Type" => "application/json",
                            "Ocp-Apim-Subscription-Key" =>
                                $this->ocpApimSubscriptionKey,
                        ])
                        ->post(
                            $this->uriBase .
                            "/persongroups/" .
                            $this->azure_grupo .
                            "/persons/" .
                            $person->personId .
                            "/persistedFaces",
                            [
                                "url" => $ruta_guardada,
                                "detectionModel" => "detection_02",
                            ]
                        );
                    $resp = $response->json();

                    if (
                        isset($resp["persistedFaceId"]) &&
                        $resp["persistedFaceId"] != ""
                    ) {
                        $persistedFaceId = $resp["persistedFaceId"];
                        $person->persistedFaceId = $persistedFaceId;
                    } else {
                        if ($resp["error"]["code"] == "InvalidImage") {
                            return response()->json([$resp, $person], 400);
                        }
                        return response()->json($resp, 400);
                    }
                } catch (HttpException $ex) {
                    echo $ex;
                }
            }
            $person->save();
        }

        try {
            $response = Http::accept("application/json")
                ->withHeaders([
                    "Ocp-Apim-Subscription-Key" =>
                        $this->ocpApimSubscriptionKey,
                ])
                ->post(
                    $this->uriBase .
                    "/persongroups/" .
                    $this->azure_grupo .
                    "/train",
                    [
                        //"url" => $ruta_guardada
                    ]
                );
            $resp = $response->json();
        } catch (HttpException $ex) {
            echo $ex;
        }
    }

    public function user($id)
    {
        return $this->success(
            Usuario::where('person_id', $id)->first()
        );
    }

    public function blockOrActivateUser(Request $request, $id)
    {
        try {
            Usuario::where('person_id', $id)->update(['state' => $request->state]);
            return $this->success('Actualizado con éxito');
        } catch (Throwable $th) {
            return $this->error($th->getMessage(), 500);
        }
    }

    public function getProfile($id)
    {
        try {
            $person = Person::where('id', $id)->first();
            return $this->success($person->apu_profile_id);
        } catch (Throwable $th) {
            return $this->error($th->getMessage(), 500);
        }
    }

    public function show(Person $person)
    {
        // $person = Person::find($person);
        return response()->success($person, 200);
    }

    public function personView(Request $request, $id)
    {
        //! UNICO ENDPOINT
        $person = Person::with([
            'contractultimateFullInformation',
            'work_contracts' => function ($query) {
                $query->where('liquidated', 1);
            },
            'usuario',
            'eps',
            'arl',
            'compensation_fund',
            'severance_fund',
            'pension_funds'
        ])->fullName()->find($id);
        return $this->success($person);

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Person  $person
     * @return \Illuminate\Http\JsonResponse
     */

    public function storeFromGlobho(ProfessionalRequest $request)
    {
        try {
            $person = Person::create($request->all());
            return $this->success(['Professional creado correctamente', $person]);
        } catch (Throwable $th) {
            return $this->error(['No se pudo crear el professional', $th->getMessage()], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Person  $person
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateFromGlobho()
    {
        try {
            $person = Person::firstWhere('identifier', request()->input('identifier'));
            if ($person) {
                $person->update(request()->all());
                return $this->success('Professional actualizado correctamente');
            }
            throw new \Exception('No se logró encontrar professional');
        } catch (Throwable $th) {
            return $this->error($th->getMessage(), 400);
        }
    }
    public function changeCompanyWorked($companyId)
    {
        $person = Person::find(Auth()->user()->person_id);
        $person->company_worked_id = $companyId;
        $person->save();

        return $this->success("success");
    }

    public function changePoint(Request $request)
    {
        $person = Person::find(auth()->user()->person_id);
        $person->dispensing_point_id = $request->dispensing_point_id;
        $person->save();
        return $this->success("success");
    }

    public function setCompaniesWork($personId, Request $req)
    {
        try {
            $companies = $req->get('companies');

            DB::table('company_person')->where('person_id', '=', $personId)->delete();

            $person = Person::find($personId);
            $person->company_worked_id = $companies[0];
            $person->save();

            foreach ($companies as $ids) {
                DB::insert('insert into company_person (company_id, person_id) values (?, ?)', [$ids, $personId]);
            }

            return $this->success('Guardado correctamente');
        } catch (Throwable $th) {
            return $this->errorResponse($th->getMessage());
        }
    }

    public function setBoardsPerson($personId, $boards)
    {
        DB::table('usuario')->where('person_id', $personId)->update(['board_id' => $boards]);
    }

    public function personCompanies($personId)
    {
        $companies = CompanyPerson::where('person_id', $personId)->get();
        return $this->success($companies);
    }

    public function personBoards($personId)
    {
        $board = Board::whereHas('user', function ($query) use ($personId) {
            $query->where('person_id', $personId);
        })->get();
        return $this->success($board);
    }

    public function personConfiguration($personId)
    {
        $folders = [
            ['value' => 0, 'text' => 'Sin acceso a carpetas'],
            ['value' => 1, 'text' => 'RRHH'],
            ['value' => 2, 'text' => 'Contabilidad'],
            ['value' => 3, 'text' => 'Juridico'],
            ['value' => 4, 'text' => 'Calidad'],
            ['value' => 5, 'text' => 'Gerencia']
        ];
        $folderId = Person::find($personId)->folder_id;

        $boards = Board::get(['id as value', 'name_board as text']);
        $boardId = Person::with('usuario')->find($personId)->usuario->board_id;

        $companies = Company::where('category', 'servicios')->orderBy('short_name')->get(['id as value', 'short_name as text']);
        $companiesId = CompanyPerson::where('person_id', $personId)->pluck('company_id')->toArray();

        $points = Dispensing::get(['Nombre As text', 'Id_Punto_Dispensacion As value']);
        $pointsId = Person::find($personId)->dispensingPoints()->pluck('dispensing_point_id')->toArray();

        $data = [
            'folders' => $folders,
            'boards' => $boards,
            'companies' => $companies,
            'points' => $points,
            'folderId' => $folderId,
            'boardId' => $boardId,
            'companiesId' => $companiesId,
            'pointsId' => $pointsId
        ];

        return $this->success($data);
    }

    public function updatePersonConfiguration(Request $request, $personId)
    {
        DB::beginTransaction();
        try {
            $validatorBoardId = $request->get('boardId') === 0 ? 'integer|between:0,0' : 'required|integer|exists:boards,id';
            $request->validate([
                'folderId' => 'required|integer|between:0,5',
                'boardId' => $validatorBoardId,
                'companiesId' => 'nullable|array',
                'companiesId.*' => 'integer|exists:companies,id',
                'pointsId' => 'nullable|array',
                'pointsId.*' => 'integer|exists:Punto_Dispensacion,Id_Punto_Dispensacion',
            ]);

            $boardId = $request->get('boardId') === 0 ? null : $request->get('boardId');

            $person = Person::with('usuario')->find($personId);
            $person->folder_id = $request->get('folderId');

            Usuario::where('person_id', $personId)->update(['board_id' => $boardId]);

            $person->companies()->sync($request->get('companiesId'));
            $person->dispensingPoints()->sync($request->get('pointsId'));
            $person->save();

            DB::commit();

            return $this->success('Actualizado con exito');
        } catch (Throwable $th) {
            DB::rollBack();
            return $this->error($th->getMessage(), 500);
        }
    }

    public function detallePerfil(Request $request)
    {
        $id = $request->input('id');

        $persona = Person::where('id', $id)
            ->orderByDesc('first_name')
            ->orderBy('second_surname')
            ->limit(1000)
            ->get();

        return $this->success($persona);
    }

    public function listaPuntoFuncionario(Request $request)
    {
        $person = Person::with('dispensingPoints')->where('identifier', $request->input('id'))->first();

        if ($person) {
            return $this->success($person->dispensingPoints);
        } else {
            return $this->success(['message' => 'Person not found'], 404);
        }
    }
}
