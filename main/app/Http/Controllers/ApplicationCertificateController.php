<?php

namespace App\Http\Controllers;

use App\Models\ApplicationCertificate;
use App\Models\ProductApplicationCertificate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\DB;



use Illuminate\Http\Request;
use App\Traits\ApiResponser;


class ApplicationCertificateController extends Controller
{
    use ApiResponser;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $page = Request()->get('page');
        $page = $page ? $page : 1;
        $pageSize = Request()->get('pageSize');
        $pageSize = $pageSize ? $pageSize : 10;

        $d = DB::table('application_certificate as ac')
            ->join('people AS pe', 'pe.id', '=', 'ac.person_id')
            ->join('patients AS pa', 'pa.id', '=', 'ac.patient_id')
            ->join('cups AS cu', 'cu.id', '=', 'ac.cups_id')
            ->join('cie10s AS cie', 'cie.id', '=', 'ac.diagnostic_id')
            ->select(
                DB::raw('CONCAT(pe.first_name," ",pe.first_surname) as Funcionario'),
                //    DB::raw(' CONCAT(P.first_name," ",P.first_surname) as nameF '),

                DB::raw('CONCAT(pa.firstname, " ",pa.surname) as Paciente'),
                DB::raw('CONCAT(cu.code, "-" ,cu.description) as Cup'),

                'ac.id',
                'ac.date',
                'ac.observation',
                'cie.description as diagnostic',
                'ac.state'


            )
            ->when(Request()->get('patient'), function ($q, $fill) {
                $q->where('pa.identifier', $fill);
            })
            ->when(Request()->get('date'), function ($q, $fill) {
                $q->where('ID.type', $fill);
            })
            ->when(Request()->get('state'), function ($q, $fill) {
                $q->where('ac.state', 'like', '%' . $fill . '%');
            })

            ->paginate($pageSize, '*', 'page', $page);

        return $this->success($d);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $data = $request->all();
            $type = '.' . $request->type;
            $data = $request->except([
                "diagnosticS",
                "productSelected",
            ]);

            $productActa = request()->get("productSelected");

            $base64 = saveBase64File($data["file"], 'actasaplicacion/', false, $type);

            $data["file"] = URL::to('/') . '/api/file?path=' . $base64;

            $cetificate = ApplicationCertificate::create([
                'person_id' => $request->person_id,
                'patient_id' => $request->patient_id,
                'cups_id' => $request->cups_id,
                'date' => $request->date,
                'diagnostic_id' => $request->diagnostic,
                'fileActa' => $base64,
                'observation' => $request->observation,
            ]);

            $id = $cetificate->id;
            foreach ($productActa as $pa) {
                $base64 = saveBase64File($pa["file1"], 'productactasaplicacion/', false, '.pdf');
                URL::to('/') . '/api/file?path=' . $base64;
                $base = saveBase64File($pa["file2"], 'productactasaplicacion/', false, '.pdf');
                URL::to('/') . '/api/file?path=' . $base;
                ProductApplicationCertificate::create([
                    'application_certificate_id' => $id,
                    'product_id' => $pa["product_id"],
                    'amount' => $pa["amount"],
                    'lote' => $pa["lote"],
                    'file1' => $base64,
                    'file2' => $base

                ]);
            }

            return $this->success('Creado con éxito');
        } catch (\Throwable $th) {
            return $this->error([$th->getMessage(), $th->getLine(), $th->getFile()], 500);
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
        //
        try {
            //code...
            $applicationC = ApplicationCertificate::find($id);
            $applicationC->state = $request->get('state');
            $applicationC->save();
            return $this->success('guardado con éxito');
        } catch (\Throwable $th) {
            //throw $th;
            return $this->success($th->getMessage(), 500);
        }
    }
}
