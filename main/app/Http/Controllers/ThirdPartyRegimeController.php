<?php

namespace App\Http\Controllers;

use App\Models\ThirdPartyRegime;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class ThirdPartyRegimeController extends Controller
{
    use ApiResponser;
    public function index()
    {
        return $this->success(ThirdPartyRegime::get(['name As text', 'id As value']));
    }

    public function paginate(Request $request)
    {
        return $this->success(
            ThirdPartyRegime::when($request->name, function ($q, $fill) {
                $q->where('name', 'like', "%$fill%");
            })
            ->when($request->status, function ($q, $fill) {
                $q->where('status', $fill);
            })
            ->orderBy('status')
            ->orderBy('name')
            ->paginate(request()->get('pageSize', 10), ['*'], 'page', request()->get('page', 1))
        );
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            ThirdPartyRegime::updateOrCreate( ['id' => $request->get('id')], $request->all());
            return $this->success('Creado con éxito');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 500);
        }
    }
}
