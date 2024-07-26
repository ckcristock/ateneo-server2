<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\DrivingLicenseJob;
use App\Models\Job;
use App\Models\Person;
use App\Models\Responsible;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Http\ResponseTrait;
use Illuminate\Support\Facades\DB;

class JobController extends Controller
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
        return $this->success(
            Job::with([
                'position' => function ($q) {
                    $q->select('name', 'id', 'dependency_id');
                },
                'salary_type' => function ($q) {
                    $q->select('id', 'name');
                },
                'position.dependency' => function ($q) {
                    $q->select('name', 'id');
                },
                'municipality' => function ($q) {
                    $q->select('name', 'id', 'department_id');
                },
                'municipality.department' => function ($q) {
                    $q->select('name', 'id');
                },
                'driving_licence_name'
            ])
                ->when($request->dependencia, function ($query, $fill) {
                    $query->whereHas('position', function ($query) use ($fill) {
                        $query->where('dependency_id', $fill);
                    });
                })
                ->when($request->municipio, function ($query, $fill) {
                    $query->whereHas('municipality', function ($query) use ($fill) {
                        $query->where('id', $fill);
                    });
                })
                ->when($request->departamento, function ($query, $fill) {
                    $query->whereHas('municipality.department', function ($query) use ($fill) {
                        $query->where('id', $fill);
                    });
                })
                ->when($request->cargo, function ($q, $fill) {
                    $q->where('position_id', $fill);
                })
                ->when($request->fecha, function ($q, $fill) {
                    $q->where('created_at', 'like', "%$fill%");
                })
                ->when($request->fecha_Inicio, function ($query, $fill) {
                    $query->where('date_start', '>=', $fill);
                })
                ->when($request->fecha_Fin, function ($query, $fill) {
                    $query->where('date_end', '<=', $fill);
                })
                ->when($request->titulo, function ($q, $fill) {
                    $q->where('title', 'like', "%$fill%");
                })
                ->where('state', 'Activo')
                ->where('company_id', $this->getCompany())
                ->whereDate('date_end', '>', DB::raw('CURDATE()'))
                ->orderBy('id', 'DESC')
                ->paginate(request()->get('pageSize', 10), ['*'], 'page', request()->get('page', 1))
        );
    }

    public function getPreview(Request $request)
    {
        $page = $request->page ?? 1;
        $pageSize = $request->pageSize ?? 10;

        return $this->success(
            Job::with([
                'position' => function ($q) {
                    $q->select('name', 'id', 'dependency_id');
                },
                'salary_type' => function ($q) {
                    $q->select('id', 'name');
                },
                'position.dependency' => function ($q) {
                    $q->select('name', 'id');
                },
                'municipality' => function ($q) use ($request) {
                    $q->select('name', 'id', 'department_id')
                        ->when($request->municipality_id, function ($q, $fill) {
                            $q->where('id', $fill);
                        });
                    ;
                },
                'work_contract_type' => function ($q) {
                    $q->select('id', 'name');
                },
                'municipality.department' => function ($q) {
                    $q->select('name', 'id');
                },
                'company'
            ])
                ->whereHas('municipality', function ($q) {
                    $q->when(request()->get('municipality_id'), function ($q, $fill) {
                        $q->where('id', '=', $fill);
                    });
                    $q->when(request()->get('department_id'), function ($q, $fill) {
                        $q->where('department_id', '=', $fill);
                    });
                })

                ->whereHas('position', function ($q) {
                    $q->when(request()->get('dependency_id'), function ($q, $fill) {
                        $q->where('dependency_id', '=', $fill);
                    });
                })
                ->when(request()->get('position'), function ($q, $fill) {
                    $q->where('title', 'like', '%' . $fill . '%');
                })
                ->when(request()->get('company_id'), function ($q, $fill) {
                    $q->where('company_id', $fill);
                })
                /*  ->when(request()->get('dependency_id'), function ($q, $fill) {
                $q->where('id', '=', $fill);
            }) */
                ->where('state', 'Activo')
                ->whereDate('date_end', '>', DB::raw('CURDATE()'))
                ->orderBy('id', 'DESC')
                ->paginate($pageSize, '*', 'page', $page)
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $job = $request->except(["drivingLicenseJob"]);
        $job['company_id'] = $this->getCompany();
        try {
            $jobDB = Job::updateOrCreate(
                ['id' => $job['id']],
                $job
            );
            $jobDB->code = "VAC" . $jobDB->id;
            $jobDB->save();
            $responsableNomina = Responsible::where('company_id', $this->getCompany())
                ->where('name', 'RESPONSABLE DE RECURSOS HUMANOS')
                ->first();
            if ($jobDB->wasRecentlyCreated) {
                Alert::create([
                    'person_id' => $request->person_id,
                    'user_id' => $responsableNomina->person_id,
                    'modal' => 0,
                    'icon' => 'fas fa-user-md',
                    'type' => 'Nueva vacante',
                    'url' => '/rrhh/vacantes-ver/' . $jobDB->id,
                    'description' => 'Se ha agregado una nueva vacante al sistema.'
                ]);
            }

            return $this->success($jobDB->id);
        } catch (\Throwable $th) {
            return $this->error([$th->getMessage(), $th->getLine(), $th->getFile()], 500);
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
        //
        return $this->success(
            Job::with([
                'position' => function ($q) {
                    $q->select('name', 'id', 'dependency_id');
                },
                'position.dependency' => function ($q) {
                    $q->select('name', 'id', 'group_id');
                },
                'position.dependency.group' => function ($q) {
                    $q->select('name', 'id');
                },
                'municipality' => function ($q) {
                    $q->select('name', 'id', 'department_id');
                },
                'municipality.department' => function ($q) {
                    $q->select('name', 'id');
                },
                'work_contract_type' => function ($q) {
                    $q->select('id', 'name');
                },
                'salary_type' => function ($q) {
                    $q->select('id', 'name');
                },
                'driving_licence_name',
                'document_type',
                'visa_type',
                'company'
            ])
                ->where('id', $id)
                ->first()
        );
    }

    public function setState($id, Request $request)
    {
        try {
            $job = Job::find($id);
            $job->state = $request->get('state');
            $job->save();
            return $this->success('actualizado exitosa');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 500);
        }
    }
}
