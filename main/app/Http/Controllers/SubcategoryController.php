<?php

namespace App\Http\Controllers;

use App\Models\AccountConfiguration;
use Illuminate\Http\Request;
use App\Traits\ApiResponser;
use App\Models\Subcategory;
use App\Models\CategoriaNueva;
use App\Models\SubcategoryVariable;
use Illuminate\Support\Facades\DB;

class SubcategoryController extends Controller
{
    use ApiResponser;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $q = Subcategory::with("category", "subcategoryVariables")
            ->when($request->idSubcategoria, function ($q, $fill) {
                $q->where("Id_Subcategoria", $fill);
            })
            ->when($request->nombre, function ($q, $fill) {
                $q->where("Nombre", 'like', "%$fill%");
            })
            ->when($request->company_id, function ($q, $fill) {
                $q->whereHas('category', function ($q2) use ($fill) {
                    $q2->where('company_id', $fill);
                });
            })
            ->when($request->Id_Categoria_Nueva, function ($q, $fill) {
                $q->whereHas('category', function ($q2) use ($fill) {
                    $q2->where('Id_Categoria_Nueva', $fill);
                });
            })
            ->when($request->nombreCategoriaNueva, function ($q, $fill) {

                $q->whereHas('category', function ($q2) use ($fill) {
                    $q2->whereHas('categoriaNueva', function ($q3) use ($fill) {
                        $q3->where('nombre', 'like', "%$fill%");
                    });
                });
            })
            ->orderBy('Fijo', 'desc')
            ->paginate(request()->get('pageSize', 10), ['*'], 'page', request()->get('page', 1));
        return $this->success($q);
    }

    public function getSubForCat($id)
    {
        return $this->success(Subcategory::where('Id_Categoria_Nueva', $id)->get(['Id_Subcategoria as value', 'Nombre as text']));
    }

    public function listSubcategories()
    {
        return $this->success(
            Subcategory::active()->select(["Nombre", "Id_Subcategoria"])
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
        /* try {
            $data = $request->except(["dynamic"]);
            $subcategory = Subcategory::create($data);
            $dynamic = request()->get("dynamic");

            foreach($dynamic as $d){
                $d["subcategory_id"] = $subcategory->id;
                SubcategoryVariable::create($d);
            }
            return $this->success("guardado con éxito");

        } catch (\Throwable $th) {
            return $this->error(['message' => $th->getMessage(), $th->getLine(), $th->getFile()], 400);
        } */
        try {
            $data = $request->except(["dynamic" /* ,"Categorias" */]);
            $subcategory = Subcategory::updateOrCreate(["Id_Subcategoria" => request()->get("Id_Subcategoria")], $data);

            if ($subcategory->wasRecentlyCreated) {
                AccountConfiguration::create([
                    'configurable_type' => Subcategory::class,
                    'configurable_id' => $subcategory->Id_Subcategoria,
                ]);
            }
            /* $dynamic = request()->get("dynamic"); */

            /* $categories=Subcategory::find($subcategory->Id_Subcategoria);
            $categories->categories()->sync(request()->get("Categorias")); */
            /* foreach ($dynamic as $d) {
                $d["subcategory_id"] = $subcategory->Id_Subcategoria;
                SubcategoryVariable::updateOrCreate(['id' => $d["id"]], $d);
            } */
            return $this->success("guardado con éxito");

        } catch (\Throwable $th) {
            return $this->error(['message' => $th->getMessage(), $th->getLine(), $th->getFile()], 400);
        }

    }

    public function turningOnOff($id, Request $request)
    {
        try {
            Subcategory::where('Id_Subcategoria', $id)->update(['Activo' => $request->activo]);
            return $this->success('Subcategoría ' . (($request->activo == 0) ? 'anulada' : 'reactivada') . ' con éxito');
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getFile() . " - " . $th->getMessage());
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
            /* Subcategory::select("Nombre As text","Id_Subcategoria As value")
            ->whereHas('categories',function($q) use ($id){
                $q->where('categoria_nueva_subcategoria.Id_Categoria_nueva', $id);
            })->get() */

            Subcategory::alias("s")->active()
                ->select("s.Nombre As text", "s.Id_Subcategoria As value")
                ->join("categoria_nueva as c", "c.Id_Categoria_nueva", "s.Id_Categoria_nueva")
                ->where("s.Id_Categoria_nueva", $id)
                ->get()
        );

    }

    public function getFieldEdit($idproducto = null, $idSubcategoria)
    {

        return $this->success(
            /* DB::select("SELECT SV.label, SV.type, VP.valor, SV.id AS subcategory_variables_id,  VP.id
            FROM subcategoria S
            INNER JOIN subcategory_variables SV  ON S.Id_Subcategoria = SV.subcategory_id
            LEFT JOIN variable_products VP ON VP.product_id = $idproducto and VP.subcategory_variables_id = SV.id
            WHERE S.Id_Subcategoria = $idSubcategoria") */
            Subcategory::alias("s")->select([
                "SV.label",
                "SV.type",
                "VP.valor",
                "SV.id AS subcategory_variables_id",
                "VP.id"
            ])
                ->join("subcategory_variables as SV", "S.Id_Subcategoria", "SV.subcategory_id")
                ->leftJoin("variable_products as VP", "VP.subcategory_variables_id", "SV.id")
                ->where("VP.product_id", $idproducto)->where("S.Id_Subcategoria", $idSubcategoria)->get()
        );

        // return $this->success(
        //     DB::select("SELECT SV.label, SV.type, VP.valor, S.Id_Subcategoria
        //     FROM subcategoria S
        //     INNER JOIN subcategory_variables SV  ON S.Id_Subcategoria = SV.subcategory_id
        //     LEFT JOIN variable_products VP ON VP.product_id = $idproducto and VP.subcategory_variables_id = SV.id
        //     where S.Id_Subcategoria = $idSubcategoria")
        // );
    }

    public function getField($id)
    {
        return $this->success(
            SubcategoryVariable::select("id", "type", "label")
                ->where("subcategory_id", $id)->get()
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
            $data = $request->except(["dynamic"]);
            Subcategory::where('Id_Subcategoria', $id)->update($data);
            $dynamic = request()->get("dynamic");

            foreach($dynamic as $d){
                $d["subcategory_id"] = $id;
                SubcategoryVariable::updateOrCreate([ 'id'=> $d["id"] ], $d );
            }
            return $this->success("guardado con éxito");

        } catch (\Throwable $th) {
            return $this->error(['message' => $th->getMessage(), $th->getLine(), $th->getFile()], 400);
        }
    }

    public function deleteVariable($id)
    {
        SubcategoryVariable::where("id", $id)->delete();
    }
}
