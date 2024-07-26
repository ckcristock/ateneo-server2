<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use App\Traits\ApiResponser;
use App\Models\LayoffsCertificate;
use App\Models\Person;
use App\Models\Responsible;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\URL;

class LayoffsCertificateController extends Controller
{
    use ApiResponser;

    private function getCompany()
    {
        return Person::find(Auth()->user()->person_id)->company_worked_id;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function paginate(Request $request)
    {
        return $this->success(
            LayoffsCertificate::with('person')
                ->when(
                    $request->name,
                    function ($q, $fill) {
                        $q->where('person_id', $fill);
                    }
                )
                ->whereHas('person.contractultimate', function ($q) {
                    $q->where('company_id', $this->getCompany());
                })
                ->paginate(Request()->get('pageSize', 5), ['*'], 'page', Request()->get('page', 1))
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

        $now = Carbon::now();
        if (
            LayoffsCertificate::where('person_id', $request->get('person_id'))
                ->whereMonth('created_at', $now->month)->exists()
        ) {
            return $this->error('Hemos encontrado una solicitud de cesantías hecha en este mes para este usuario', 409);
        } else {
            $base64 = saveBase64File($request["document"], 'cesantias/', false, '.pdf');
            $file = URL::to('/') . '/api/file?path=' . $base64;
            LayoffsCertificate::create([
                'reason_withdrawal' => $request->reason_withdrawal,
                'person_id' => $request->person_id,
                'reason' => $request->reason,
                'document' => $file,
                'monto' => $request->monto,
                'valormonto' => $request->valormonto,
            ]);
            return $this->success('correcto');
        }
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
        LayoffsCertificate::where('id', $id)
            ->update(['state' => $request->state]);
        return $this->success('Actualizado con éxito');
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

    public function pdf($id)
    {
        $company = Company::find($this->getCompany());
        $date = Carbon::now()->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY');
        $layoffs_certificate = LayoffsCertificate::where('id', $id)
            ->with('person', 'reason_withdrawal_list')->first();
        $image = $company->page_heading;
        $datosCabecera = (object) array(
            'Titulo' => 'Certificación cesantías',
            'Codigo' => $layoffs_certificate->code,
            'Fecha' => $layoffs_certificate->created_at,
            'CodigoFormato' => $layoffs_certificate->format_code
        );
        $responsableRhName = $this->getResponsableRhName();
        $pdf = PDF::loadView('pdf.certificado_cesantias', [
            'date' => $date,
            'company' => $company,
            'layoffs_certificate' => $layoffs_certificate,
            'datosCabecera' => $datosCabecera,
            'image' => $image,
            'responsableRhName' => $responsableRhName
        ]);
        return $pdf->download('comprobante.pdf');
    }
}
