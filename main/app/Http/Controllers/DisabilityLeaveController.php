<?php

namespace App\Http\Controllers;

use App\Models\DisabilityLeave;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class DisabilityLeaveController extends Controller
{
    use ApiResponser;
    //
    public function index()
    {
        return $this->success(DisabilityLeave::all(['id as value', 'concept as text']));
    }

    public function paginate()
    {
        $novedades =
            DisabilityLeave::when(
                Request()->get('novelty'),
                function ($q, $fill) {
                    $q->where('novelty', 'like', '%' . $fill . '%');
                }
            )
                ->paginate(request()->get('pageSize', 10), ['*'], 'page', request()->get('page', 1));

        foreach ($novedades as $novedad) {
            $cuenta = $this->consultaAPI($novedad->accounting_account, 'codigo');
            $novedad->cuenta_contable = $cuenta;
        }
        return $this->success($novedades);
    }

    public function store(Request $request)
    {
        try {
            $noveltyTypes = DisabilityLeave::updateOrCreate(['id' => $request->get('id')], $request->all());
            return ($noveltyTypes->wasRecentlyCreated) ? $this->success('Creado con éxito') : $this->success('Actualizado con éxito');
        } catch (\Throwable $th) {
            return response()->json([$th->getMessage(), $th->getLine()]);
        }
    }

    public function cuentasContablesList()
    {
        ///http://inventario.sigmaqmo.com/php/plancuentas/filtrar_cuentas.php?coincidencia=as&tipo=codigo
        $novedades = DisabilityLeave::all();

        foreach ($novedades as $novedad) {
            $cuenta = $this->consultaAPI($novedad->accounting_account, 'codigo');
            $novedad->cuenta_contable = $cuenta;
        }

        return $this->success($novedades);
    }

    private function consultaAPI($coincidencia, $tipo)
    {
        $direcccion = 'http://inventario.sigmaqmo.com/php/plancuentas/filtrar_cuentas.php?coincidencia=' . $coincidencia . '&tipo=' . $tipo . '';
        $data = json_decode(file_get_contents($direcccion), true);
        return $data;
    }

}
