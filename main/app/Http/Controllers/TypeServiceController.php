<?php

namespace App\Http\Controllers;

use App\Models\Formality;
use App\Models\TypeService;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class TypeServiceController extends Controller
{
	//
	use ApiResponser;

	public function index(Request $request)
	{
		return $this->success(
			TypeService::when($request->is_service, function ($q, $fill) {
				$q->where('is_service', $fill);
			})
				->get(['name As text', 'id As value'])
		);
	}
	public function allByFormality($formality)
	{
		return $this->success(Formality::find($formality)->typeServices);
	}

	public function store(Request $request)
	{
		try {
			$typeService = TypeService::create($request->all());
			return $this->success(['message' => 'Servicio creado correctamente', 'model' => $typeService]);
		} catch (\Throwable $th) {
			return response()->json([$th->getMessage(), $th->getLine()]);
		}
	}

	public function update(Request $request, TypeService $typeService)
	{
		try {
			$typeService = TypeService::find(request()->get('id'));
			$typeService->update(request()->all());
			return $this->success('Servicio actualizado correctamente');
		} catch (\Throwable $th) {
			return response()->json([$th->getMessage(), $th->getLine()]);
		}
	}


	public function destroy($id)
	{
		try {
			$typeService = TypeService::findOrFail($id);
			$typeService->delete();
			return $this->success('Servicio eliminado correctamente', 204);
		} catch (\Throwable $th) {
			return response()->json([$th->getMessage(), $th->getLine()]);
		}
	}
}
