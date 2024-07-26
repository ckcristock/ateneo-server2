<?php

namespace App\Http\Controllers;

use App\Models\Formulario;
use App\Models\FormularioResponse;
use App\Traits\ApiResponser;
use Exception;
use Illuminate\Http\Request;

class FormularioController extends Controller
{

    use ApiResponser;

    /**
     * Get Form  by id
     * @param $id Form
     * @return  \Illuminate\Http\JsonResponse
     */
    public function getFormulario(Formulario $formulario)
    {
        return   $this->success($formulario);
    }

    /**
     * Get Form  by id
     * @param $id Form
     * @return  \Illuminate\Http\JsonResponse
     */
    public function saveResponse()
    {
        try {
            foreach (json_decode(request()->get('data')) as  $response) {
                FormularioResponse::create([
                    'formulario_id' => 1,
                    'question_id' => $this->verifyResponseType($response),
                    'company_id' => request()->get('idCompany'),
                    'sede_id' => request()->get('idSede'),
                    'response' => $response->Respuesta,
                ]);
            }
            return   $this->success('Datos guarados correctamente');
        } catch (\Throwable $e) {
            return  $this->error($e->getMessage(), $e->getCode());
        }
    }

    public function verifyResponseType(Object  $response): int
    {
        if (isset($response->formulario_id)) {
            return  $response->id;
        }

        if (isset($response->question_id)) {
            return  $response->question_id;
        }

        if (!isset($response->question_id) && !isset($response->question_id)) {
            throw new Exception("El tipo de pregunta no ha sido verificado", 400);
        }

        return 0;
    }
}
