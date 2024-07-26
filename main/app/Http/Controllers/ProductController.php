<?php

namespace App\Http\Controllers;

use App\Models\AccountConfiguration;
use App\Models\ActividadProducto;
use App\Models\CategoryVariable;
use Illuminate\Http\Request;
use App\Traits\ApiResponser;
use App\Models\Product;
use App\Models\SubcategoryVariable;
use App\Models\Unit;
use App\Models\VariableProduct;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

class ProductController extends Controller
{
    use ApiResponser;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function index(Request $request)
    {
        $data = Product::alias('p')->join('Subcategoria as s', 's.Id_Subcategoria', 'p.Id_Subcategoria')
            ->join('Categoria_Nueva as c', 'c.Id_Categoria_Nueva', 's.Id_Categoria_Nueva')
            ->leftJoin('inventary_dotations as ido', 'ido.product_id', 'p.Id_Producto')
            ->leftJoin('units as u', 'u.id', 'p.Unidad_Medida')
            ->leftJoin('packagings as pc', 'pc.id', 'p.Embalaje_id')
            ->select(
                'p.Id_Producto',
                'p.Codigo_Barras',
                'p.Presentacion',
                'p.Unidad_Medida',
                'p.Id_Categoria',
                'p.Id_Subcategoria',
                'p.Nombre_Comercial',
                'p.Imagen as Foto',
                'p.Embalaje',
                'p.Embalaje_id',
                'c.Nombre as Categoria',
                's.Nombre as Subcategoria',
                'pc.name as Embalaje',
                'u.name as Nom_unidad_medida',
                'ido.status',
                'ido.id as id_inventary_dotations',
                'ido.code as code_inventary_dotations',
                'p.Estado',
                'p.Referencia'
            );

        $data->when($request->company_id, function ($q, $fill) {
            $q->where("p.company_id", $fill);
        })
            ->when($request->categoria, function ($q, $fill) {
                $q->where("p.Id_Categoria", '=', $fill);
            })
            ->when($request->subcategoria, function ($q, $fill) {
                $q->where("p.Id_Subcategoria", '=', $fill);
            })
            ->when($request->nombre, function ($q, $fill) {
                $q->where("p.Nombre_Comercial", 'like', "%$fill%");
            })
            ->when($request->estado, function ($q, $fill) {
                $q->where("p.Estado", '=', $fill);
            })
            ->when($request->imagen, function ($q, $fill) {
                $q->where('p.Imagen', (($fill == 'con') ? "!=" : "="), null);
            });

        return $this->success(($request->id !== null) ?
            $data->where("p.Id_Producto", $request->id)->first() :
            $data->paginate(request()->get('pageSize', 10), ['*'], 'page', request()->get('page', 1)));
    }

    public function getActividad()
    {
        return $this->success(
            ActividadProducto::alias("ar")->with([
                "funcionario" => function ($q) {
                    $q->select("id", "image")->completeName();
                }
            ])
                ->where("Id_Producto", request()->get('id'))->orderBy('Fecha', 'desc')->get()
        );
    }

    public function getMaterials(Request $request)
    {
        /** Funcion que obtiene los productos de la categoría materia prima y subcategoria materiales para llamar
         * en materiales comerciales de apu y para mostrar en la parametrización de apu materiales corte agua y corte láser
         */
        $product = Product::where('Id_Categoria', 1)
            ->where('Id_Subcategoria', 1)
            ->when($request->name, function ($q, $fill) {
                $q->where('Nombre_Comercial', 'like', '%' . $fill . '%');
            })
            /* ->when($request->limit, function ($query) {
                return $query->limit(10);
            }) */
            ->get(['Id_Producto as id', 'Id_Producto as value', DB::raw('UPPER(Nombre_Comercial) AS text')]);
        return $this->success($product);
    }

    public function getVars()
    {

        $producto = request()->get('producto');
        $query = [];
        if ($producto !== null) {
            $query["cat"] = VariableProduct::alias("vp")
                ->select("vp.id as vp_id", "cv.id as cv_id", "label", "type", "required", "valor")
                ->join('category_variables as cv', 'cv.id', 'vp.category_variables_id')
                ->where("vp.product_id", $producto)->get();

            if (count($query["cat"]) == 0) {
                $query["cat"] = CategoryVariable::select("category_variables.id as cv_id", "label", "type", "required")
                    ->join('Producto as p', 'p.Id_Categoria', "category_variables.category_Id")
                    ->where("Id_Producto", $producto)->get();
            }

            $query["subcat"] = VariableProduct::alias("vp")
                ->select("vp.id as vp_id", "sv.id as sv_id", "label", "type", "required", "valor")
                ->join('subcategory_variables as sv', 'sv.id', 'vp.subcategory_variables_id')
                ->where("vp.product_id", $producto)->get();

            if (count($query["subcat"]) == 0) {
                $query["subcat"] = SubcategoryVariable::select("subcategory_variables.id as sv_id", "label", "type", "required")
                    ->join('Producto as p', 'p.Id_Subcategoria', 'subcategory_variables.Subcategory_Id')
                    ->where("Id_Producto", $producto)->get();
            } /*  */
        } else {
            $query["cat"] = CategoryVariable::select("id as cv_id", "label", "type", "required")
                ->where("Category_Id", request()->get('categoria'))->get();

            $query["subcat"] = SubcategoryVariable::select("id as sv_id", "label", "type", "required")
                ->where("Subcategory_Id", request()->get('subcategoria'))->get();
        }
        return $this->success($query);
    }


    public function listarProductos()
    {
        return $this->success(
            Product::alias('P')
                ->select(
                    "P.Nombre_Comercial",
                    "P.Id_Producto",
                    "P.Embalaje_id",
                    "P.Cantidad_Presentacion",
                    DB::raw("IFNULL(cp.Costo_Promedio,0) AS Costo")
                )
                ->leftJoin("Costo_Promedio AS cp", function ($join) {
                    $join->on("cp.Id_Producto", "P.Id_Producto");
                })
                ->whereNotNull("P.Codigo_Barras")
                ->whereRaw("P.Estado='Activo'")
                ->whereRaw("P.Codigo_Barras!=''")
                ->when(request()->get("categoria"), function ($q, $fill) {
                    $q->where("P.Id_Categoria", '=', $fill);
                })
                ->when(request()->get("subcategoria"), function ($q, $fill) {
                    $q->where("P.Id_Subcategoria", '=', $fill);
                })
                ->get()
        );
    }

    public function cambiarEstado(Request $request)
    {
        try {
            Product::where('Id_Producto', $request->id)->update(['Estado' => $request->estado]);
            return $this->success('Producto ' . (($request->estado == "Inactivo") ? 'anulado' : 'activado') . ' con éxito');
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getFile() . " - " . $th->getMessage());
        }
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
            $data = $request->except(["camposCategoria", "camposSubcategoria", "user_id", "camposFlag"]);

            $variablesProd = [
                "cat" => [
                    "registros" => $request->camposCategoria,
                    "modelo" => CategoryVariable::all(),
                    "campo_id" => "category_variables_id",
                    "titulo" => "categoría",
                    "camposFlag" => $request->camposFlag["cat"]
                ],
                "subcat" => [
                    "registros" => $request->camposSubcategoria,
                    "modelo" => SubcategoryVariable::all(),
                    "campo_id" => "subcategory_variables_id",
                    "titulo" => "subcategoría",
                    "camposFlag" => $request->camposFlag["subcat"]
                ]
            ];

            $type = $request->Foto["type"];
            if (!is_null($type)) {
                if (in_array($type, ['jpeg', 'jpg', 'png'])) {
                    $base64 = saveBase64($request->Foto["file"], 'fotos_productos/', true);
                    $url = URL::to('/') . '/api/image?path=' . $base64;
                } else {
                    throw new Exception(
                        "No se ha encontrado un formato de imagen válido ($type), revise e intente nuevamente"
                    );
                }
                $data['Imagen'] = $url;
            }
            unset($data["Foto"]);

            $data_actual = (isset($request->Id_Producto)) ? Product::find($request->Id_Producto) : [];

            $product = Product::updateOrCreate(["Id_Producto" => $data["Id_Producto"]], $data);
            if ($product->wasRecentlyCreated) {
                AccountConfiguration::create([
                    'configurable_type' => Product::class,
                    'configurable_id' => $product->Id_Producto,
                ]);
            }

            if (isset($data["Unidad_Medida"])) {
                $data_actual["Unidad_Medida"] = Unit::find($data_actual["Unidad_Medida"])->name;
                $data["Unidad_Medida"] = Unit::find($data["Unidad_Medida"])->name;
            }
            if ($product->wasRecentlyCreated) {
                ActividadProducto::create([
                    "Id_Producto" => $product->Id_Producto,
                    "Person_Id" => $request->user_id,
                    "Detalles" => "El producto '" . $product->Nombre_Comercial . "' fue ingresado al sistema",
                ]);
            } else {
                foreach ($data as $key => $campo) {
                    if ($campo !== $data_actual[$key]) {
                        ActividadProducto::create([
                            "Id_Producto" => $product->Id_Producto,
                            "Person_Id" => $request->user_id,
                            "Detalles" => (isset($data_actual[$key])) ? "El campo '" . $key . "' fue modificado de '" . $data_actual[$key] . "' a '" . $campo . "'." :
                                "El campo '" . $key . "' fue ingresado con el valor '" . $campo . "'.",
                        ]);
                    }
                }
            }

            foreach ($variablesProd as $variableProd) {
                foreach ($variableProd["registros"] as $dataVariablesProd) {
                    $data_actual = (isset($data["id"])) ? VariableProduct::find($data["id"]) : [];

                    $dataVariablesProd["product_id"] = $product->Id_Producto;
                    $varProd = VariableProduct::updateOrCreate(['id' => $dataVariablesProd["id"]], $dataVariablesProd);

                    $nomVar = $variableProd["modelo"]->fresh()->find($varProd[$campo["campo_id"]])["label"];

                    if ($varProd->wasRecentlyCreated) {   // Si la variable de producto no tenia un valor antes.
                        ActividadProducto::create([
                            "Id_Producto" => $product->Id_Producto,
                            "Person_Id" => $request->user_id,
                            "Detalles" => "La variable de " . $variableProd["titulo"] . " '$nomVar' fue " .
                                (($variableProd["camposFlag"]) ? "creada e " : "") . "ingresada al sistema con el valor '" . $varProd->valor . "'.",
                        ]);
                    } else {
                        foreach ($dataVariablesProd as $key_dataVariablesProd => $campo_dataVariablesProd) {
                            if ($campo_dataVariablesProd !== $data_actual[$key]) {
                                ActividadProducto::create([
                                    "Id_Producto" => $product->Id_Producto,
                                    "Person_Id" => $request->user_id,
                                    "Detalles" => "La variable de " . $variableProd["titulo"] . " '$nomVar' fue " .
                                        ((isset($data_actual[$key_dataVariablesProd])) ? "modificada de '" . $data_actual[$key_dataVariablesProd] . "' a '" : "ingresada con el valor '") . $campo_dataVariablesProd . "'.",
                                ]);
                            }
                        }
                    }
                }
            }

            return $this->success("guardado con éxito");
        } catch (\Throwable $th) {
            return $this->error(['message' => $th->getMessage(), $th->getLine(), $th->getFile()], 400);
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
        try {
            /* $datos = $request->all();
            $data = $request->except(["dynamic","Status","Codigo","Producto_Dotacion_Tipo","id_inventary_dotations"]);
            $dynamic = request()->get("dynamic");
            // var_dump($dynamic);
            $product = Product::where('Id_Producto', $id)->update($data);

            foreach ($dynamic as $d) {
                $d['product_id'] = $id;
                VariableProduct::updateOrCreate(['id' => $d["id"]], $d);
            }

            $datos['product_id'] = $datos["Id_Producto"];
            $datos['product_dotation_type_id'] = $datos["Producto_Dotation_Type_Id"];
            $datos['name'] = $datos["Nombre_Comercial"];
            $datos['code'] = $datos["Codigo"];
            $datos['type'] = $datos["Tipo"];
            $datos['status'] = $datos["Status"];
            $datos['cost'] = 0;
            $datos['stock'] = 0;

            InventaryDotation::updateOrCreate(['id' => $datos["id_inventary_dotations"]],$datos); */

            return $this->success("guardado con éxito");
        } catch (\Throwable $th) {
            return $this->error(['message' => $th->getMessage(), $th->getLine(), $th->getFile()], 400);
        }
    }

    public function getProductActa()
    {

    }
}
