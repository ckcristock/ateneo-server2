<?php

namespace App\Http\Controllers;

use App\Models\ActividadProducto;
use App\Models\CategoryVariable;
use App\Models\CategoriaNueva;
use App\Models\Packaging;
use App\Models\Person;
use App\Models\Product;
use App\Models\Subcategory;
use App\Models\SubcategoryVariable;
use App\Models\Tax;
use App\Models\Unit;
use App\Models\VariableProduct;
use App\Traits\ApiResponser;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

class ProductNewController extends Controller
{

    use ApiResponser;

    private function getCompany()
    {
        return Person::find(Auth()->user()->person_id)->company_worked_id;
    }

    public function paginate(Request $request)
    {
        $categoryId = $request->has('categoryId') ? $request->categoryId : 1;
        // Obtener los productos paginados
        $products = Product::with([
            'subcategory',
            'category' => function ($query) {
                $query->where("company_id", $this->getCompany());
            },
            'unit',
            'packaging',
            'tax'
        ])
            ->where("Id_Categoria", $categoryId)
            ->when($request->subcategoria, function ($q, $fill) {
                $q->where("Id_Subcategoria", $fill);
            })
            ->when($request->nombre, function ($q, $fill) {
                $q->where(function ($query) use ($fill) {
                    $query->orWhere("Nombre_Comercial", 'like', "%$fill%")
                        ->orWhere("Nombre_General", 'like', "%$fill%")
                        ->orWhere("Referencia", 'like', "%$fill%");
                });
            })
            ->when($request->estado, function ($q, $fill) {
                $q->where("Estado", '=', $fill);
            })
            ->when($request->imagen, function ($q, $fill) {
                $q->where('Imagen', (($fill == 'con') ? "!=" : "="), null);
            })
            ->orderBy('Nombre_Comercial')
            ->paginate($request->get('pageSize', 10), ['*'], 'page', $request->get('page', 1));

        // Obtener los IDs de los productos paginados
        $productIds = $products->pluck('Id_Producto')->toArray();

        // Obtener productos con sus variables dinámicas
        list($productosConVariables, $variablesLabels) = $this->obtenerProductosConVariables($productIds);

        // Añadir las variables dinámicas y campos adicionales a los productos paginados
        foreach ($products as $product) {
            $productoConVariables = $productosConVariables->firstWhere('Id_Producto', $product->Id_Producto);

            if ($productoConVariables) {
                $product->variables = $productoConVariables->variables;
            }

            // Verificar si la categoría tiene has_lote o has_expiration_date en true
            $hasLote = $product->category->has_lote;
            $hasExpirationDate = $product->category->has_expiration_date;

            // Añadir los campos adicionales si aplican
            if ($hasLote || $hasExpirationDate) {
                if ($hasLote) {
                    $product->Lote = $product->Lote ?? null;
                }
                if ($hasExpirationDate) {
                    $product->Fecha_Vencimiento = $product->Fecha_Vencimiento ?? null;
                }
            }
        }

        // Convertir la colección de productos paginados a un array
        $productsArray = $products->toArray();


        $productsArray['data'] = array_map(function ($product) {
            if (!$product['category']['has_lote']) {
                unset($product['Lote']);
            }
            if (!$product['category']['has_expiration_date']) {
                unset($product['Fecha_Vencimiento']);
            }
            return $product;
        }, $productsArray['data']);

        // Añadir las etiquetas de variables a la respuesta
        $productsArray['variables_labels'] = $variablesLabels;

        return $this->success($productsArray);
    }

    private function obtenerProductosConVariables($productosIds)
    {
        // Obtener productos con sus variables y etiquetas asociadas
        $productos = Product::whereIn('Id_Producto', $productosIds)
            ->with(['variableProducts.categoryVariable'])
            ->get();

        $variablesLabels = [];
        foreach ($productos as $producto) {
            $variables = [];
            foreach ($producto->variableProducts as $variableProduct) {
                $variables[$variableProduct->categoryVariable->label] = $variableProduct->valor;
                $variablesLabels[] = $variableProduct->categoryVariable->label;
            }
            $producto->variables = $variables;
        }

        return [$productos, $variablesLabels];
    }


    public function getMaterials()
    {
        //*Funcion que obtiene los productos de la categoría materia prima y subcategoria materiales para llamar en materiales comerciales de apu y para mostrar en la parametrización de apu materiales corte agua y corte láser

        $product = Product::where('Id_Categoria', 1)
            ->where('Id_Subcategoria', 1)
            ->get(['Id_Producto as id', 'Id_Producto as value', 'Nombre_Comercial as text']);
        return $this->success($product);
    }

    public function getDataCreate()
    {
        $unit = Unit::get(['name As text', 'id AS value']);
        $packaging = Packaging::get(['id as value', 'name as text']);
        $categories =
            CategoriaNueva::with([
                'subcategories' => function ($q) {
                    $q->select('Id_Subcategoria', 'Id_Categoria_Nueva', 'Id_Subcategoria as value', 'Nombre as text');
                }
            ])
                ->where('company_id', $this->getCompany())
                ->get(['Id_Categoria_Nueva', 'Id_Categoria_Nueva as value', 'Nombre as text', 'general_name']);
        $taxes = Tax::get(['Id_Impuesto as value', 'Valor as text']);
        return $this->success([
            'categories' => $categories,
            'packagings' => $packaging,
            'units' => $unit,
            'taxes' => $taxes
        ]);
    }

    public function getVariablesCat($id)
    {
        return $this->success(CategoriaNueva::find($id)->categoryVariables);
    }

    public function getVariablesSubCat($id)
    {
        return $this->success(Subcategory::find($id)->subcategoryVariables);
    }

    public function index()
    {
    }

    public function listarProductos(Request $request)
    {
        return $this->success(
            Product::where('Estado', 'Activo')
                ->with('unit', 'packaging', 'tax')
                ->when($request->categoria, function ($q, $fill) {
                    $q->where("Id_Categoria", $fill);
                })
                ->when($request->subcategoria, function ($q, $fill) {
                    $q->where("Id_Subcategoria", $fill);
                })
                ->get()
        );
    }

    public function store(Request $request)
    {
        $data = $request->except(['category_variables', 'subcategory_variables']);
        $category_variables = $request->category_variables;
        $subcategory_variables = $request->subcategory_variables;

        if (!$request->Id_Producto) {
            if ($request->Imagen) {
                $base64 = saveBase64($request->Imagen, 'fotos_productos/', true, '.' . explode("/", $request->typeFile)[1]);
                $data['Imagen'] = URL::to('/') . '/api/image?path=' . $base64;
            }
        } else {
            $actualData = Product::find($request->Id_Producto);
            if ($request->Imagen != $actualData->Imagen) {
                $base64 = saveBase64($request->Imagen, 'fotos_productos/', true, '.' . explode("/", $request->typeFile)[1]);
                $data['Imagen'] = URL::to('/') . '/api/image?path=' . $base64;
            }
        }
        $product = Product::updateOrCreate(["Id_Producto" => $data["Id_Producto"]], $data);
        if ($product->wasRecentlyCreated) {
            $this->createActivity(
                $product->Id_Producto,
                "El producto '" . $product->Nombre_Comercial . "' fue ingresado al sistema",
            );
            foreach ($data as $key => $campo) {
                $this->newDataActivity($campo, $key, $product->Id_Producto);
            }
        } else {
            foreach ($data as $key => $campo) {
                if ($campo !== $actualData[$key]) {
                    $this->changeDataActivity($campo, $key, $product->Id_Producto, $actualData[$key]);
                }
            }
        }
        $all_variables = array_merge($category_variables, $subcategory_variables);
        foreach ($all_variables as $value) {
            if ($value['subcategory_variables_id']) {
                $label = SubcategoryVariable::find($value['subcategory_variables_id'])->label;
            } else {
                $label = CategoryVariable::find($value['category_variables_id'])->label;
            }
            if ($value['id']) {
                $actualDataVariable = VariableProduct::find($value['id']);
            }
            $value['product_id'] = $product->Id_Producto;
            $variable = VariableProduct::updateOrCreate(['id' => $value['id']], $value);
            if ($variable->wasRecentlyCreated) {
                $this->newDataActivity($variable->valor, $label, $product->Id_Producto);
            } else {
                if ($value['valor'] != $actualDataVariable['valor']) {
                    $this->changeDataActivity($variable->valor, $label, $product->Id_Producto, $actualDataVariable->valor);
                }
            }
        }
        return $this->success('Se ha guardado correctamente');
    }

    function newDataActivity($campo, $key, $id)
    {
        switch ($key) {
            case 'Id_Producto':
                break;
            case 'typeFile':
                break;
            case 'Presentacion':
                $key = 'presentacion';
                $this->createActivity(
                    $id,
                    "El campo '" . $key . "' fue ingresado con el valor '" . $campo . "'."
                );
                break;
            case 'impuesto_id':
                $key = 'impuesto';
                $campo = Tax::find($campo)->Valor;
                $this->createActivity(
                    $id,
                    "El campo '" . $key . "' fue ingresado con el valor '" . $campo . "'."
                );
                break;
            case 'Nombre_Comercial':
                $key = 'nombre';
                $this->createActivity(
                    $id,
                    "El campo '" . $key . "' fue ingresado con el valor '" . $campo . "'."
                );
                break;
            case 'Referencia':
                $key = 'referencia';
                $this->createActivity(
                    $id,
                    "El campo '" . $key . "' fue ingresado con el valor '" . $campo . "'."
                );
                break;
            case 'Unidad_Medida':
                $key = 'unidad de medida';
                $campo = Unit::find($campo)->name;
                $this->createActivity(
                    $id,
                    "El campo '" . $key . "' fue ingresado con el valor '" . $campo . "'."
                );
                break;
            case 'Precio':
                $key = 'precio';
                $this->createActivity(
                    $id,
                    "El campo '" . $key . "' fue ingresado con el valor '" . $campo . "'."
                );
                break;
            case 'Cantidad':
            case 'Codigo_Barras':
                $key = 'código de barras';
                $this->createActivity(
                    $id,
                    "El campo '" . $key . "' fue ingresado con el valor '" . $campo . "'."
                );
                break;
            case 'Imagen':
                $key = 'imagen';
                if ($campo) {
                    $this->createActivity(
                        $id,
                        "El campo '" . $key . "' fue ingresado."
                    );
                }
                break;
            case 'Id_Categoria':
                $key = 'categoría';
                $campo = CategoriaNueva::find($campo)->Nombre;
                $this->createActivity(
                    $id,
                    "El campo '" . $key . "' fue ingresado con el valor '" . $campo . "'."
                );
                break;
            case 'Referencia':
                $key = 'referencia';
                $this->createActivity(
                    $id,
                    "El campo '" . $key . "' fue ingresado con el valor '" . $campo . "'."
                );
                break;
            case 'Estado':
                $key = 'estado';
            case 'Id_Subcategoria':
                $key = 'subcategoría';
                $campo = Subcategory::find($campo)->Nombre;
                $this->createActivity(
                    $id,
                    "El campo '" . $key . "' fue ingresado con el valor '" . $campo . "'."
                );
                break;
            case 'Embalaje_id':
                $key = 'embalaje';
                $campo = Packaging::find($campo)->name;
                $this->createActivity(
                    $id,
                    "El campo '" . $key . "' fue ingresado con el valor '" . $campo . "'."
                );
                break;
            default:
                $this->createActivity(
                    $id,
                    "El campo '" . $key . "' fue ingresado con el valor '" . $campo . "'."
                );
                break;
        }
    }

    function changeDataActivity($campo, $key, $id, $oldData)
    {
        switch ($key) {
            case 'Id_Producto':
                break;
            case 'typeFile':
                break;
            case 'Presentacion':
                $key = 'presentacion';
                $this->createActivity(
                    $id,
                    (isset($oldData))
                    ? "El campo '" . $key . "' fue modificado de '" . $oldData . "' a '" . $campo . "'."
                    : "El campo '" . $key . "' fue ingresado con el valor '" . $campo . "'."
                );
                break;
            case 'Nombre_Comercial':
                $key = 'nombre';

                $this->createActivity(
                    $id,
                    (isset($oldData))
                    ? "El campo '" . $key . "' fue modificado de '" . $oldData . "' a '" . $campo . "'."
                    : "El campo '" . $key . "' fue ingresado con el valor '" . $campo . "'."
                );
                break;
            case 'impuesto_id':
                $key = 'impuesto';
                $campo = Tax::find($campo)->Valor ?? '';
                $oldData = Tax::find($oldData)->Valor ?? '';
                $this->createActivity(
                    $id,
                    (isset($oldData))
                    ? "El campo '" . $key . "' fue modificado de '" . $oldData . "' a '" . $campo . "'."
                    : "El campo '" . $key . "' fue ingresado con el valor '" . $campo . "'."
                );
                break;
            case 'Precio':
                $key = 'precio';
                $this->createActivity(
                    $id,
                    (isset($oldData))
                    ? "El campo '" . $key . "' fue modificado de '" . $oldData . "' a '" . $campo . "'."
                    : "El campo '" . $key . "' fue ingresado con el valor '" . $campo . "'."
                );
                break;
            case 'Unidad_Medida':
                $key = 'unidad de medida';
                $campo = Unit::find($campo)->name;
                $oldData = Unit::find($oldData)->name;
                $this->createActivity(
                    $id,
                    (isset($oldData))
                    ? "El campo '" . $key . "' fue modificado de '" . $oldData . "' a '" . $campo . "'."
                    : "El campo '" . $key . "' fue ingresado con el valor '" . $campo . "'."
                );
                break;
            case 'Cantidad':
            case 'Codigo_Barras':
                $key = 'código de barras';
                $this->createActivity(
                    $id,
                    (isset($oldData))
                    ? "El campo '" . $key . "' fue modificado de '" . $oldData . "' a '" . $campo . "'."
                    : "El campo '" . $key . "' fue ingresado con el valor '" . $campo . "'."
                );
                break;
            case 'Referencia':
                $key = 'referencia';
                $this->createActivity(
                    $id,
                    (isset($oldData))
                    ? "El campo '" . $key . "' fue modificado de '" . $oldData . "' a '" . $campo . "'."
                    : "El campo '" . $key . "' fue ingresado con el valor '" . $campo . "'."
                );
                break;
            case 'Imagen':
                $key = 'imagen';
                if ($campo) {
                    $this->createActivity(
                        $id,
                        (isset($oldData))
                        ? "El campo '" . $key . "' fue modificado."
                        : "El campo '" . $key . "' fue ingresado."
                    );
                }
                break;
            case 'Id_Categoria':
                $key = 'categoría';
                $campo = CategoriaNueva::find($campo)->Nombre;
                $oldData = CategoriaNueva::find($oldData)->Nombre;
                $this->createActivity(
                    $id,
                    (isset($oldData))
                    ? "El campo '" . $key . "' fue modificado de '" . $oldData . "' a '" . $campo . "'."
                    : "El campo '" . $key . "' fue ingresado con el valor '" . $campo . "'."
                );
                break;
            case 'Referencia':
                $key = 'referencia';
                $this->createActivity(
                    $id,
                    (isset($oldData))
                    ? "El campo '" . $key . "' fue modificado de '" . $oldData . "' a '" . $campo . "'."
                    : "El campo '" . $key . "' fue ingresado con el valor '" . $campo . "'."
                );
                break;
            case 'Estado':
                $key = 'estado';
            case 'Id_Subcategoria':
                $key = 'subcategoría';
                $campo = Subcategory::find($campo)->Nombre;
                $oldData = Subcategory::find($oldData)->Nombre;
                $this->createActivity(
                    $id,
                    (isset($oldData))
                    ? "El campo '" . $key . "' fue modificado de '" . $oldData . "' a '" . $campo . "'."
                    : "El campo '" . $key . "' fue ingresado con el valor '" . $campo . "'."
                );
                break;
            case 'Embalaje_id':
                $key = 'embalaje';
                $campo = Packaging::find($campo)->name;
                $oldData = Packaging::find($oldData)->name ?? '';
                $this->createActivity(
                    $id,
                    (isset($oldData))
                    ? "El campo '" . $key . "' fue modificado de '" . $oldData . "' a '" . $campo . "'."
                    : "El campo '" . $key . "' fue ingresado con el valor '" . $campo . "'."
                );
                break;
            default:
                $this->createActivity(
                    $id,
                    (isset($oldData))
                    ? "El campo '" . $key . "' fue modificado de '" . $oldData . "' a '" . $campo . "'."
                    : "El campo '" . $key . "' fue ingresado con el valor '" . $campo . "'."
                );
                break;
        }
    }

    function createActivity($id, $details)
    {
        //dd($details);
        ActividadProducto::create([
            "Id_Producto" => $id,
            "Person_Id" => auth()->user()->person_id,
            "Detalles" => $details
        ]);
    }

    public function show($id)
    {
        return $this->success(
            Product::with('subcategory', 'category', 'unit', 'activity', 'variables', 'packaging', 'tax')
                ->find($id)
        );
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }
}
