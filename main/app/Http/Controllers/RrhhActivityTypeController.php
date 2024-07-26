<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\RrhhActivityType;
use Illuminate\Http\Request;
use App\Traits\ApiResponser;

class RrhhActivityTypeController extends Controller
{
    use ApiResponser;

    private function getCompany()
    {
        return Person::find(Auth()->user()->person_id)->company_worked_id;
    }

    public function index()
    {
        return $this->success(
            RrhhActivityType::where('company_id', $this->getCompany())
                ->paginate(request()->get('pageSize', 10), ['*'], 'page', request()->get('page', 1))
        );
    }
    public function all()
    {
        return $this->success(
            RrhhActivityType::get(['name As text', 'id As value'])
        );
    }
    public function actives()
    {
        return $this->success(
            RrhhActivityType::where('state', 'activo')
                ->where('company_id', $this->getCompany())
                ->get(['name As text', 'id As value'])
                ->map(function ($activityType) {
                    $activityType->text = strtoupper($activityType->text);
                    return $activityType;
                })
        );
    }

    public function store(Request $request)
    {
        try {
            $data = $request->all();
            $data['company_id'] = $this->getCompany();
            $activity = RrhhActivityType::updateOrCreate(['id' => $request->get('id')], $data);
            return ($activity->wasRecentlyCreated) ? $this->success('Creado con éxito') : $this->success('Actualizado con éxito');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 500);
        }
    }

    public function setState(Request $request)
    {
        try {
            $type = RrhhActivityType::findOrFail($request->get('id'));
            $type->state = $request->get('state');
            $type->save();
            return $this->success('Actualización con éxtio');
        } catch (\Throwable $th) {
            //throw $th;
            return $this->error($th->getMessage(), 500);
        }
    }
}
