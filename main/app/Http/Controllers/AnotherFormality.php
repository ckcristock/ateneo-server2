<?php

namespace App\Http\Controllers;

use App\Models\CallIn;
use App\Traits\ApiResponser;

class AnotherFormality extends Controller
{
    use ApiResponser;

    public function store()
    {
        try {
            $data = Request()->all();
            $call = CallIn::find($data['call_id']);

            $call->update([
                'Tipo_Tramite' => $data['formality_id'],
                'Tipo_Servicio' => $data['type_service_id'],
                'Observation' => $data['observation'],
                'status' => 'Atendida'
            ]);
            return $this->success('Actualización con éxito');

        } catch (\Throwable $th) {
            return $this->error('Ha ocurrido un error' . $th->getMessage(), 401);

        }


    }
}
