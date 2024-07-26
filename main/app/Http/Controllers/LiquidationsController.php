<?php

namespace App\Http\Controllers;

use App\Models\Liquidation;
use App\Models\Person;
use App\Models\Responsible;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Barryvdh\DomPDF\Facade\Pdf;

class LiquidationsController extends Controller
{
    use ApiResponser;

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */

     private function getCompany()
    {
        return Person::find(Auth()->user()->person_id)->company_worked_id;
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $this->validateRequest($request);
            $personId = $validatedData['person_id'];
            $this->updatePersonStatus($personId);
            Liquidation::create($validatedData);
            return $this->success('Funcionario liquidado con éxito');
        } catch (ValidationException $e) {
            return $this->error('Error de validación: ' . $e->getMessage(), 422);
        } catch (\Exception $e) {
            return $this->error('Ha ocurrido un error al liquidar el funcionario', 500);
        }
    }

    private function validateRequest(Request $request)
    {
        return $request->validate([
            'person_id' => 'required|exists:people,id',
            'motivo' => 'required|string',
            'justa_causa' => 'required|in:si,no',
            'fecha_contratacion' => 'required|date',
            'fecha_terminacion' => 'required|date|after_or_equal:fecha_contratacion',
            'dias_liquidados' => 'required|integer|min:1',
            'dias_vacaciones' => 'required|numeric|min:0',
            'salario_base' => 'required|numeric|min:0',
            'vacaciones_base' => 'required|numeric|min:0',
            'cesantias_base' => 'required|numeric|min:0',
            'dominicales_incluidas' => 'required|in:Si,No',
            'cesantias_anterior' => 'nullable|numeric|min:0',
            'intereses_cesantias' => 'nullable|numeric|min:0',
            'otros_ingresos' => 'nullable|numeric|min:0',
            'prestamos' => 'nullable|numeric|min:0',
            'otras_deducciones' => 'nullable|numeric|min:0',
            'notas' => 'nullable|string',
            'ingresos_adicionales' => 'nullable|numeric|min:0',
            'deducciones_adicionales' => 'nullable|numeric|min:0',
            'valor_dias_vacaciones' => 'nullable|numeric|min:0',
            'valor_cesantias' => 'required|numeric|min:0',
            'valor_prima' => 'required|numeric|min:0',
            'sueldo_pendiente' => 'nullable|numeric|min:0',
            'auxilio_pendiente' => 'nullable|numeric|min:0',
            'otros' => 'nullable|numeric|min:0',
            'salud' => 'nullable|numeric|min:0',
            'pension' => 'nullable|numeric|min:0',
            'total' => 'nullable|numeric|min:0',
        ]);
    }

    private function updatePersonStatus($personId)
    {
        Person::where('id', $personId)->update(['status' => 'liquidado']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */

    public function show($id)
    {
        return $this->success(Person::where('id', $id)->with('liquidation')->first());
    }

    private function getResponsableRhName()
    {
        $companyWorkedId = $this->getCompany();
        $responsableRh = Responsible::where('company_id', $companyWorkedId)
                                    ->where('name', 'LIKE', '%RESPONSABLE DE RECURSOS HUMANOS%')
                                    ->first();
        if ($responsableRh) {
            $person = Person::find($responsableRh->person_id);
            if ($person) {
                return $person->full_name;
            }
        }
        return 'Responsable de RRHH no encontrado';
    }

    //download liquidation
    public function download($personId)
    {
        $liquidation = Liquidation::with('person')->where('person_id', $personId)->latest()->first();
        $header = (object) array(
            'Titulo' => 'Liquidación',
            'Codigo' => $liquidation->code ?? '',
            'Fecha' => $liquidation->created_at,
            'CodigoFormato' => $liquidation->format_code ?? ''
        );
        $responsableRhName = $this->getResponsableRhName();
        $pdf = PDF::loadView('pdf.liquidation', [
            'data' => $liquidation,
            'datosCabecera' => $header,
            'responsableRhName' => $responsableRhName,
        ]);
        $fileName = 'liquidacion-' . str_replace(' ', '-', strtolower($liquidation->person->full_names)) . '-' . $liquidation->created_at->format('Y-m-d') . '.pdf';
        return $pdf->download($fileName);
    }
}
