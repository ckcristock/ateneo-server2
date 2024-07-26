<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Person;
use Illuminate\Http\Request;
use App\Traits\ApiResponser;
use App\Models\WorkCertificate;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use NumberFormatter;
use App\Models\Responsible;

class WorkCertificateController extends Controller
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
            WorkCertificate::with('person')
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
            WorkCertificate::where('person_id', $request->get('person_id'))
                ->whereMonth('created_at', $now->month)->exists()
        ) {
            return $this->error('Hemos encontrado un certificado laboral solicitado en este mes para este usuario', 409);
        } else {
            $json = json_encode($request->information, true);
            $request->merge(['information' => $json]);
            WorkCertificate::create($request->all());
            return $this->success('correcto');
        }
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
        $date = Carbon::now()->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY');
        $work_certificate = WorkCertificate::where('id', $id)->first();
        $informations = json_decode($work_certificate->information);
        $formatterES = new NumberFormatter("es", NumberFormatter::SPELLOUT);
        $date2 = Carbon::now();
        $company = Company::with('documentTypeInfo')->find($this->getCompany());
        $funcionario = Person::where('id', $work_certificate->person_id)
            ->with('contractultimate')->name()->first();
        $date_of_admission = Carbon::parse($funcionario->contractultimate->date_of_admission)->locale('es')->isoFormat('DD MMMM YYYY');
        $salario_numeros = $formatterES->format($funcionario->contractultimate->salary);
        $addressee = $work_certificate->addressee ?: 'A QUIEN INTERESE';
        $gener = $funcionario->gener === 'Masculino' ? 'certifica que el señor' : 'certifica que la señora';
        $image = $company->page_heading;
        $datosCabecera = (object) array(
            'Titulo' => 'Certificación laboral',
            'Codigo' => $work_certificate->code,
            'Fecha' => $work_certificate->created_at,
            'CodigoFormato' => $work_certificate->format_code
        );
        $responsableRhName = $this->getResponsableRhName();
        $pdf = PDF::loadView('pdf.certificado_laboral', [
            'date' => $date,
            'date2' => $date2,
            'company' => $company,
            'gener' => $gener,
            'funcionario' => $funcionario,
            'date_of_admission' => $date_of_admission,
            'work_certificate' => $work_certificate,
            'informations' => $informations,
            'salario_numeros' => $salario_numeros,
            'addressee' => $addressee,
            'datosCabecera' => $datosCabecera,
            'image' => $image,
            'responsableRhName' => $responsableRhName
        ]);
        return $pdf->download('certificado.pdf');
    }
}
