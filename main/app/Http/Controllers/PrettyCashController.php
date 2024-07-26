<?php

namespace App\Http\Controllers;

use App\Models\PrettyCash;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class PrettyCashController extends Controller
{
    use ApiResponser;

    public function paginate(Request $request)
    {
        $data = PrettyCash::with(
            [
                'person' => function ($q) {
                    $q->select('id', 'identifier', 'first_name', 'first_surname');
                },

                'user' => function ($q) {
                    $q->select('id', 'person_id')
                        ->with([
                            'person' => function ($q) {
                                $q->select('id', 'identifier', 'first_name', 'first_surname');
                            }
                        ]);
                },
                'accountPlan' => function ($q) {
                    $q->select('*')->with('balance');
                }
            ]
        )
            ->when($request->name, function ($q, $fill) {
                $q->where('description', 'like', "%$fill%");
            })
            ->when($request->person_id, function ($q, $fill) {
                $q->where('person_id', $fill);
            })
            ->paginate(request()->get('pageSize', 10), ['*'], 'page', request()->get('page', 1));

        return $this->success($data);
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
            $data["user_id"] = auth()->user()->id;
            PrettyCash::create($data);

            return $this->success('Creado con éxito');
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage(), 402);
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
        return $this->success(PrettyCash::where('id', $id)->first());
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
        PrettyCash::find($id)->update($request->all());
        return $this->success('Actualizado correctamente');
    }
}
