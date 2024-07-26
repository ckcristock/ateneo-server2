<?php

namespace App\Http\Controllers;

use App\Models\AccountConfiguration;
use App\Models\CategoryVariable;
use Illuminate\Http\Request;
use App\Models\CategoriaNueva;
use App\Models\Person;
use App\Traits\ApiResponser;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    use ApiResponser;

    private function getCompany()
    {
        return Person::find(Auth()->user()->person_id)->company_worked_id;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->success(
            CategoriaNueva::with("subcategory")
                ->where('company_id', $this->getCompany())
                ->active()
                ->get()

        );
    }

    public function indexForSelect($filterColumn = null, $filterValue = null)
    {
        $query = CategoriaNueva::active()
            ->where('company_id', $this->getCompany());

        if ($filterColumn !== null && $filterValue !== null) {
            $query->where($filterColumn, $filterValue);
        }

        $categorias = $query->get(['Id_Categoria_Nueva as value', 'Nombre as text']);

        return $this->success($categorias);
    }


    public function listCategories(Request $request)
    {
        return $this->success(
            CategoriaNueva::when($request->company_id, function ($q, $fill) {
                $q->where("company_id", $fill);
            })
                ->orderBy('Id_Categoria_Nueva', 'ASC')
                ->active()
                ->get(['Nombre As text', 'Id_Categoria_Nueva As value'])
        );
    }

    public function paginate()
    {
        return $this->success(
            CategoriaNueva::with("subcategory", "categoryVariables")
                ->when(request()->get("nombre"), function ($q, $fill) {
                    $q->where("Nombre", 'like', '%' . $fill . '%');
                })
                ->when(request()->get("company_id"), function ($q, $fill) {
                    $q->where("company_id", $fill);
                })
                ->when(request()->get("compraInternacional"), function ($q, $fill) {
                    $q->where("Compra_Internacional", "=", $fill);
                })
                ->orderBy('Fijo', 'desc')
                ->orderByDesc('Id_Categoria_Nueva')
                ->paginate(request()->get('pageSize', 10), ['*'], 'page', request()->get('page', 1))
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
        try {
            $dynamic = request()->get("dynamic");

            // Validación de los nuevos campos
            $request->validate([
                'receives_barcode' => 'boolean',
                'is_stackable' => 'boolean',
                'is_inventory' => 'boolean',
                'is_listed' => 'boolean',
                'has_lote' => 'boolean',
                'has_expiration_date' => 'boolean',
                'request_purchase' => 'boolean',
            ]);

            $category = CategoriaNueva::updateOrCreate(['Id_Categoria_Nueva' => request()->get('Id_Categoria_Nueva')], $request->except(["dynamic", "general_name"]));

            if ($category->wasRecentlyCreated) {
                AccountConfiguration::create([
                    'configurable_type' => CategoriaNueva::class,
                    'configurable_id' => $category->Id_Categoria_Nueva,
                ]);
            }
            $category->update([
                'receives_barcode' => $request->input('receives_barcode', false),
                'is_stackable' => $request->input('is_stackable', false),
                'is_inventory' => $request->input('is_inventory', false),
                'is_listed' => $request->input('is_listed', false),
                'has_lote' => $request->input('has_lote', false),
                'has_expiration_date' => $request->input('has_expiration_date', false),
                'request_purchase' => $request->input('request_purchase', false),
            ]);

            $variables = [];

            foreach ($dynamic as $d) {
                $d["category_id"] = $category->Id_Categoria_Nueva;
                $variables[] = CategoryVariable::updateOrCreate(['id' => $d["id"]], $d);
            }

            $generalName = request()->get("general_name");

            foreach ($generalName as $key => $value) {
                foreach ($variables as $variable) {
                    if ($variable->label == $value) {
                        $generalName[$key] = $variable->id;
                    }
                }
            }

            $category["general_name"] = json_encode($generalName);

            $category->save();

            return ($category->wasRecentlyCreated) ? $this->success('Creado con éxito') : $this->success('Actualizado con éxito');
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getFile() . " - " . $th->getMessage());
        }
    }

    public function turningOnOff($id, Request $request)
    {
        try {
            $category = CategoriaNueva::find($id);
            $category->Activo = $request->activo;
            $category->save();
            $category->subcategory()->update(['Activo' => $request->activo]);
            return $this->success('Categoría ' . (($request->activo == 0) ? 'anulada' : 'reactivada') . ' con éxito');
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getFile() . " - " . $th->getMessage());
        }
    }

    public function getField(Request $request, $id)
    {
        $reception = $request->has('reception');
        $lists = $request->has('lists');

        $query = CategoryVariable::select("id as cv_id", "label", "type", "required", "reception", "lists")
            ->where("Category_Id", $id);

        if ($reception) {
            $query->where('reception', true);
        }

        if ($lists) {
            $query->where('lists', true);
        }

        $results = $query->get();

        return $this->success($results);
    }


    public function deleteVariable($id)
    {
        CategoryVariable::where("id", $id)->delete();
    }
}
