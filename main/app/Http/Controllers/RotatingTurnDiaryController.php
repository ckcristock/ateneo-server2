<?php

namespace App\Http\Controllers;

use App\Models\DiarioTurnoFijo;
use App\Models\DiarioTurnoRotativo;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class RotatingTurnDiaryController extends Controller
{
    use ApiResponser;

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            DiarioTurnoRotativo::where('id', $id)->update($request->all());
            return $this->success('actualizado');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 401);
        }
    }
}
