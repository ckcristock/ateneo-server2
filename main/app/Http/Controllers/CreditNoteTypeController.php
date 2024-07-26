<?php

namespace App\Http\Controllers;

use App\Models\CreditNoteType;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class CreditNoteTypeController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->success(CreditNoteType::where('status', 'Activo')->get(['id as value', 'name as text']));
    }

    public function paginate(Request $request)
    {
        return $this->success(
            CreditNoteType::orderBy('status')
                ->when($request->name, function ($q, $fill) {
                    $q->where('name', 'like', "%$fill%");
                })
                ->paginate(request()->get('pageSize', 15), ['*'], 'page', request()->get('page', 1))
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
        try {
            $creditNoteType = CreditNoteType::updateOrCreate(['id' => $request->id], $request->all());
            return $this->success($creditNoteType->wasRecentlyCreated ? 'Creado con éxito.' : 'Actualizado con éxito.');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage() . ' msg: ' . $th->getLine() . ' ' . $th->getFile(), 204);
        }
    }
}
