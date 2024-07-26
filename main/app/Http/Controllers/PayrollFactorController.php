<?php

namespace App\Http\Controllers;

use App\Exports\PayrollFactorExport;
use App\Models\DisabilityLeave;
use App\Models\DocumentPayrollFactor;
use App\Models\PayrollFactor;
use App\Models\Person;
use App\Traits\ApiResponser;
use App\Traits\PayrollFactorDates;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

class PayrollFactorController extends Controller
{
    //
    use PayrollFactorDates;
    use ApiResponser;

    private function getCompany()
    {
        return Person::find(Auth()->user()->person_id)->company_worked_id;
    }

    public function store(Request $request)
    {
        $files = $request->get('files');
        if ($request->get('id') || $this->customValidate($request->all())) {
            $values = $request->get('id') ? $request->all() : $this->pushFlag($request->all());
            $payrollFactor = PayrollFactor::updateOrCreate(['id' => $request->get('id')], $values);
            if ($request->filled('files')) {
                foreach ($files as $file) {
                    $base64 = $this->saveFiles($file, 'payroll-factor/');
                    $url = URL::to('/') . '/api/file?path=' . $base64;
                    DocumentPayrollFactor::create([
                        'payroll_factor_id' => $payrollFactor->id,
                        'file' => $url,
                        'name' => $file['name'],
                        'type' => $file['type'],
                    ]);
                }
            }
            return $this->success('Novedad creada correctamente');
        }
        return $this->error('El funcionario ya se encuentra con novedades registradas en este periodo', 422);
    }

    function saveFiles($file, $path)
    {
        $file_info = $file;
        $extension = pathinfo($file_info['name'], PATHINFO_EXTENSION);
        $allowed_extensions = ["pdf", "png", "jpeg", "jpg", "doc", "docx", "xlsx", "mp3", "mp4", "wav", "mkv"];
        if (!in_array(strtolower($extension), $allowed_extensions)) {
            return response()->json(['error' => 'Tipo de archivo no permitido'], 422);
        }
        $file_content = base64_decode(
            preg_replace(
                "#^data:[a-z]+/[\w\+]+;base64,#i",
                "",
                $file['file']
            )
        );
        $file_path = $path . Str::random(30) . time() . '.' . $extension;
        Storage::disk()->put($file_path, $file_content, "public");
        return $file_path;
    }

    public function count()
    {
        return $this->success(Person::with(
            [
                'payroll_factors' => function ($q) {
                    $q->when(request()->get('personfill'), function ($q, $fill) {
                        $q->where('person_id', 'like', '%' . $fill . '%');
                    });
                    $q->when(request()->get('date_end'), function ($q, $fill) {
                        $q->where('date_start', '>=', request()->get('date_start'))
                            ->where('date_end', '<=', request()->get('date_end'));
                    });
                    $q->when(request()->get('date_start'), function ($q, $fill) {
                        $q->where('date_start', '>=', request()->get('date_start'))
                            ->where('date_end', '<=', request()->get('date_end'));
                    });
                    // $q->where('person_id', '=', request()->get('personfill'));
                    // $q->where('date_start', '>=', request()->get('date_start'))
                    //     ->where('date_end', '<=', request()->get('date_end'));
                },
                'payroll_factors.disability_leave' => function ($q) {
                },
                'contractultimate' => function ($q) {
                }
            ]
        )
            ->whereHas('payroll_factors', function ($q) {
                $q->when(request()->get('personfill'), function ($q, $fill) {
                    $q->where('person_id', 'like', '%' . $fill . '%');
                });
                $q->when(request()->get('date_end'), function ($q, $fill) {
                    $q->where('date_start', '>=', request()->get('date_start'))
                        ->where('date_end', '<=', request()->get('date_end'));
                });
                $q->when(request()->get('date_start'), function ($q, $fill) {
                    $q->where('date_start', '>=', request()->get('date_start'))
                        ->where('date_end', '<=', request()->get('date_end'));
                });
                // $q->where('person_id', '=', request()->get('personfill'));
                // $q->where('date_start', '>=', request()->get('date_start'))
                //     ->where('date_end', '<=', request()->get('date_end'));
            })
            ->whereHas('contractultimate', function ($q) {
                $q->where('company_id', $this->getCompany());
            })
            ->get());
    }

    public function indexByPeople()
    {
        return $this->success(
            PayrollFactor::with('person', 'disability_leave', 'documents')
                ->when(request()->get('personfill'), function ($q, $fill) {
                    $q->where('person_id', $fill);
                })
                ->when(request()->get('date_end'), function ($q, $fill) {
                    $q->where('date_start', '>=', request()->get('date_start'))
                        ->where('date_end', '<=', request()->get('date_end'));
                })
                ->when(request()->get('date_start'), function ($q, $fill) {
                    $q->where('date_start', '>=', request()->get('date_start'))
                        ->where('date_end', '<=', request()->get('date_end'));
                })
                ->when(request()->get('type'), function ($q, $fill) {
                    $q->where('disability_leave_id', 'like', '%' . $fill . '%');
                })
                ->orderByDesc('created_at')
                ->whereHas('person.contractultimate', function ($query) {
                    $query->where('company_id', $this->getCompany());
                })
                ->paginate(Request()->get('pageSize', 10), ['*'], 'page', Request()->get('page', 1))
        );

    }


    public function pushFlag(array $request): array
    {
        $request['sum'] = (DisabilityLeave::find($request['disability_leave_id'], ['sum']))->sum;
        return $request;
    }

    public function payrollFactorDownload(Request $request)
    {
        return Excel::download(new PayrollFactorExport($request, $this->getCompany()), 'users.xlsx');
    }
}
