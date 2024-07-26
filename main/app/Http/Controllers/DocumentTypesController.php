<?php

namespace App\Http\Controllers;

use App\Models\DocumentTypes;
use App\Traits\ApiResponser;
use App\Models\TypeDocument;
use Illuminate\Http\Request;

class DocumentTypesController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->success(DocumentTypes::where('status', '=', 'Activo')
            ->get(['name As text', 'id As value', 'code']));
    }

    public function paginate()
    {
        $data = Request()->all();
        $page = key_exists('page', $data) ? $data['page'] : 1;
        $pageSize = key_exists('pageSize', $data) ? $data['pageSize'] : 5;
        return $this->success(
            DocumentTypes::when(Request()->get('name'), function ($q, $fill) {
                $q->where('name', 'like', '%' . $fill . '%');
            })
                ->orderBy('status', 'asc')
                ->orderBy('name', 'asc')
                ->paginate($pageSize, ['*'], 'page', $page)
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
            $typeDocument = DocumentTypes::updateOrCreate(['id' => $request->get('id')], $request->all());
            return ($typeDocument->wasRecentlyCreated) ? $this->success('Creado con éxito') : $this->success('Actualizado con éxito');
        } catch (\Throwable $th) {
            return response()->json([$th->getMessage(), $th->getLine()]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, DocumentTypes $typeDocument)
    {
        try {
            $typeDocument = DocumentTypes::find(request()->get('id'));
            $typeDocument->update(request()->all());
            return $this->success('Documento actualizado correctamente');
        } catch (\Throwable $th) {
            return response()->json([$th->getMessage(), $th->getLine()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $typeDocument = DocumentTypes::findOrFail($id);
            $typeDocument->delete();
            return $this->success('Documento eliminado correctamente', 204);
        } catch (\Throwable $th) {
            return response()->json([$th->getMessage(), $th->getLine()]);
        }
    }
}
