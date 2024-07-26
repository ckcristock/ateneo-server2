<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class ApplicantController extends Controller
{
    use ApiResponser;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $req)
    {
        //
        return $this->success(
            Applicant::when($req->get('job_id'), function ($q, $fill) {
                $q->where('job_id', $fill);
            })
                ->with('visaType')
                ->with('drivingLicense')
                ->orderBy('id', 'DESC')
                ->get()
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
        //
        try {
            $data = $request->all();
            $base64 = saveBase64File($data["file"], 'curriculum/', false);
            $data["curriculum"] = $base64;

            Applicant::create($data);
            return $this->success('Guardado con éxito: ');
        } catch (\Throwable $th) {
            return $this->error('error: ' . $th->getLine() . ' ' . $th->getMessage(), 500);
        }
    }

    public function donwloadCurriculum($id)
    {
        $applicant = Applicant::find($id);
        return Storage::download($applicant->curriculum);
    }
}
