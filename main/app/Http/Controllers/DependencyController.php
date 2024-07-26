<?php

namespace App\Http\Controllers;

use App\Models\Dependency;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class DependencyController extends Controller
{
    use ApiResponser;

    public function index(Request $request)
    {
        return   $this->success(
            Dependency::with('group')
                ->when($request->get('group_id'), function ($q, $p) {
                    $q->where('group_id', $p);
                })
                ->when($request->company_id, function ($q, $fill) {
                    $q->whereHas('group', function ($q2) use ($fill) {
                        $q2->where('company_id', $fill);
                    });
                })
                ->get(['id as value', 'name as text', 'group_id'])
        );
    }

    public function store(Request $request)
    {
        try {
            $dependency = Dependency::updateOrCreate(['id' => $request->get('id')], $request->all());
            return ($dependency->wasRecentlyCreated) ? $this->success('Creado con éxito') : $this->success('Actualizado con éxito');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 500);
        }
    }

    public function dependencies()
    {
        return $this->success(
            Dependency::all(['id as value', 'name as text'])
        );
    }
}
