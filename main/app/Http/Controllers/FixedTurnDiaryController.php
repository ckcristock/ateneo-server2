<?php

namespace App\Http\Controllers;

use App\Models\DiarioTurnoFijo;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class FixedTurnDiaryController extends Controller
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
            DiarioTurnoFijo::where('id', $id)->update($request->all());
            return $this->success('actualizado');
        } catch (\Throwable $th) {
            //throw $th;
            return $this->error($th->getMessage(), 401);
        }
    }
}
