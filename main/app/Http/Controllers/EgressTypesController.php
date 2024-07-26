<?php

namespace App\Http\Controllers;

use App\Models\CountableDeduction;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class EgressTypesController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->success(CountableDeduction::all(['concept as text', 'id as value']));
    }


    public function paginate(Request $request)
    {
        $page = $request->input('page', 1);
        $pageSize = $request->input('pageSize', 10);

        $egressTypes = CountableDeduction::with('accounts')
            ->when(
                $request->has('name'),
                function ($q, $fill) {
                    $q->where('concept', 'like', "%$fill%");
                }
            )
            ->where('company_id', getCompanyWorkedId())
            ->paginate($pageSize, ['*'], 'page', $page);
        return $this->success($egressTypes);
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
            $data = $request->all();
            $data['company_id'] = getCompanyWorkedId();
            $egressTypes = CountableDeduction::updateOrCreate(['id' => $request->get('id')], $data);
            return ($egressTypes->wasRecentlyCreated) ? $this->success('Creado con exito') : $this->success('Actualizado con exito');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), $th->getCode());
        }
    }
}
