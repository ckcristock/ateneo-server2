<?php

namespace App\Http\Controllers;

use App\Models\DiarioTurnoFijo;
use App\Models\DiarioTurnoRotativo;
use App\Models\DiaryEdit;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class DiaryEditController extends Controller
{
    use ApiResponser;

    public function updateDiary(Request $request)
    {
        try {
            $diariableType = $request->fixed_turn_id ? DiarioTurnoFijo::class : DiarioTurnoRotativo::class;
            DiaryEdit::create([
                'diariable_id' => $request->id,
                'diariable_type' => $diariableType,
                'hours' => $request->working_hours,
                'justification' => $request->justification,
                'person_id' => auth()->user()->person_id
            ]);
            return $this->success('Horas modificadas correctamente');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 200);
        }

    }
}
