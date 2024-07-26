<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Person;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    use ApiResponser;

    private function getCompany()
    {
        return Person::find(Auth()->user()->person_id)->company_worked_id;
    }

    public function index(Request $request)
    {
        if ($request->company_id) {
            return $this->success(
                Group::where('company_id', $request->company_id)
                    ->get(['id as value', 'name as text'])
            );
        } else {
            return $this->success(
                Group::where('company_id', $this->getCompany())
                    ->get(['id as value', 'name as text'])
            );
        }
    }

    public function getGroupCompany(Request $request)
    {
        $companiId = $request->filled('company_id') ? $request->get('company_id') : $this->getCompany();

        $groups = Group::with('dependencies.positions')
            ->where('company_id', $companiId)
            ->get();

        $transformedGroups = $groups->map(function ($group) {
            return [
                'value' => $group->id,
                'text' => $group->name,
                'dependencies' => $group->dependencies->map(function ($dependency) {
                    return [
                        'value' => $dependency->id,
                        'text' => $dependency->name,
                        'positions' => $dependency->positions->map(function ($position) {
                            return [
                                'value' => $position->id,
                                'text' => $position->name,
                            ];
                        }),
                    ];
                }),

            ];
        });

        return $this->success($transformedGroups);
    }

    public function store(Request $request)
    {
        try {
            $group = Group::updateOrCreate(['id' => $request->get('id')], $request->all());
            return ($group->wasRecentlyCreated) ? $this->success('Creado con éxito') : $this->success('Actualizado con éxito');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 500);
        }
    }
    public function destroy($id)
    {
        Group::destroy(['id' => $id]);
        return $this->success('eliminado con exito');
    }
}
