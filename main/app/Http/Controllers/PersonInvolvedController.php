<?php

namespace App\Http\Controllers;

use App\Models\DisciplinaryProcess;
use App\Models\MemorandumInvolved;
use App\Models\PersonInvolved;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class PersonInvolvedController extends Controller
{
    use ApiResponser;

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $type = '.' . $request->type;
            if ($request->type == 'jpeg' || $request->type == 'jpg' || $request->type == 'png') {
                $base64 = saveBase64($request->file, 'evidencia/', true);
                $url = URL::to('/') . '/api/image?path=' . $base64;
            } else {
                $base64 = saveBase64File($request->file, 'evidencia/', false, $type);
                $url = URL::to('/') . '/api/file?path=' . $base64;
            }
            $annotation = PersonInvolved::create([
                'user_id' => auth()->user()->id,
                'observation' => $request->observation,
                'file' => $url,
                'file_type' => $request->type,
                'disciplinary_process_id' => $request->disciplinary_process_id,
                'person_id' => $request->person_id
            ]);
            $personName = $annotation->person->full_names;
            $data = (object) [
                'title' => 'Nuevo involucrado',
                'description' => "Se ha agregado a {$personName} como involucrado al proceso.",
                'icon' => 'fas fa-exclamation-circle',
                'type' => 'Involucrado',
                'person_id' => $request->person_id,
                'historiable_type' => DisciplinaryProcess::class,
                'historiable_id' => $request->disciplinary_process_id,
            ];
            addHistory($data);
            foreach ($request->memorandums as $memorandum) {
                MemorandumInvolved::create([
                    'person_involved_id' => $annotation['id'],
                    'memorandum_id' => $memorandum['id']
                ]);
            }
            return $this->success('Guardado con éxito');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 500);
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
        return $this->success(
            PersonInvolved::where('disciplinary_process_id', $id)
                ->where('state', 'activo')
                ->with([
                    'user' => function ($q) {
                        $q->select('id', 'person_id');
                    }
                ])
                ->with([
                    'person' => function ($q) {
                        $q->select('id', 'first_name', 'second_name', 'first_surname', 'second_surname');
                    }
                ])
                ->with('memorandumInvolved')
                ->get()
        );
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
        try {
            $annotation = PersonInvolved::with('person')->find($id);
            $annotation->fill($request->all());
            $annotation->save();
            $data = (object) [
                'person_id' => auth()->user()->person_id,
                'title' => 'Se anuló un involucrado',
                'description' => "Se anuló a {$annotation->person->full_names} del proceso disciplinario con el siguiente motivo: {$request->reason} .",
                'icon' => 'fas fa-exclamation-circle',
                'type' => 'Proceso disciplinario',
                'historiable_type' => DisciplinaryProcess::class,
                'historiable_id' => $annotation->disciplinary_process_id,
            ];
            addHistory($data);
            return $this->success('Actualizado con éxito');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 500);
        }
    }

}
