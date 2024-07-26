<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\Product;
use App\Models\ProductPurchaseRequest;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestActivity;

use App\Models\QuotationPurchaseRequest;
use App\Traits\ApiResponser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;


class PurchaseRequestController extends Controller
{

    use ApiResponser;

    public function getProducts(Request $request)
    {
        $productos = Product::where('Id_Categoria', $request->category_id)->with(['unit', 'variableProductsSinRecepcion.categoryVariable'])
            ->when($request->search, function ($query, $fill) {
                $query->where('Nombre_Comercial', 'like', "%$fill%");
            })->get(['*', 'Nombre_Comercial as name'])->take(10);

        [$productosConVariables, $variablesLabels] = getVariablesProductos($productos);

        return $this->success(
            [
                'productos' => $productosConVariables,
                'variables' => $variablesLabels,
            ]
        );
    }

    public function paginate(Request $request)
    {
        return $this->success(
            PurchaseRequest::with('productPurchaseRequest', 'person', 'quotationPurchaseRequest')
                ->when($request->code, function ($q, $fill) {
                    $q->where('code', 'like', "%$fill%");
                })
                ->when($request->start_created_at, function ($q, $fill) use ($request) {
                    $q->where('created_at', '>=', $fill)
                        ->where('created_at', '<=', $request->end_created_at . ' 23:59:59');
                })
                ->when($request->start_expected_date, function ($q, $fill) use ($request) {
                    $q->whereBetween('expected_date', [$fill, $request->end_expected_date]);
                })
                ->when($request->status, function ($q, $fill) {
                    $q->where('status', 'like', "%$fill%");
                })
                ->when($request->funcionario, function ($q, $fill) {
                    $q->whereHas('person', function ($query) use ($fill) {
                        $query->where('first_name', 'like', "%$fill%");
                    });
                })
                ->orderByDesc('created_at')
                ->paginate(request()->get('pageSize', 10), ['*'], 'page', request()->get('page', 1))
        );
    }

    public function paginatePurchase(Request $request)
    {
        // Obtener el id de la categoría desde el request
        $categoryId = $request->input('category_id');

        // Consulta ajustada con filtros y agrupación
        $purchaseRequests = PurchaseRequest::with([
            'productPurchaseRequest' => function ($query) {
                $query->select('id', 'product_id', 'purchase_request_id', 'ammount')
                    ->with('product');
            },
            'person',
            'quotationPurchaseRequest',
            'user.person', 
            'dispensationPoint' 
        ])
            ->whereDoesntHave('ordenesCompraNacional', function ($query) {
                $query->where('status', 'activo');
            })
            ->when($categoryId, function ($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            })
            ->when($request->code, function ($q, $fill) {
                $q->where('code', 'like', "%$fill%");
            })
            ->when($request->start_created_at, function ($q, $fill) use ($request) {
                $q->where('created_at', '>=', $fill)
                    ->where('created_at', '<=', $request->end_created_at . ' 23:59:59');
            })
            ->when($request->start_expected_date, function ($q, $fill) use ($request) {
                $q->whereBetween('expected_date', [$fill, $request->end_expected_date]);
            })
            ->when($request->status, function ($q, $fill) {
                $q->where('status', 'like', "%$fill%");
            })
            ->when($request->funcionario, function ($q, $fill) {
                $q->whereHas('person', function ($query) use ($fill) {
                    $query->where('first_name', 'like', "%$fill%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate($request->get('pageSize', 10), ['*'], 'page', $request->get('page', 1));

        // Procesar los resultados para añadir campos adicionales
        foreach ($purchaseRequests as $purchaseRequest) {
            foreach ($purchaseRequest->productPurchaseRequest as $product) {
                // Añadir información adicional al producto
                $product->dispensation_point = $purchaseRequest->dispensationPoint->Nombre ?? null;
                $product->requested_by = $purchaseRequest->user->person->full_name ?? null;
                $product->request_date = $purchaseRequest->created_at ?? null;
            }
        }

        return $this->success($purchaseRequests);
    }



    public function getProductsByPurchaseRequestIds(Request $request)
    {
        $purchaseRequestIds = $request->input('purchase_request_ids');

        // Validar que los purchase_request_ids sean proporcionados
        if (!$purchaseRequestIds || !is_array($purchaseRequestIds)) {
            return $this->error('purchase_request_ids are required and should be an array.', 400);
        }

        // Obtener los PurchaseRequests con los productos, el punto de dispensación y los campos adicionales
        $purchaseRequests = PurchaseRequest::with([
            'productPurchaseRequest' => function ($query) {
                $query->select('id', 'product_id', 'purchase_request_id', 'ammount')
                    ->with('product');
            },
            'user.person', // Para obtener el nombre de la persona que lo solicita
            'dispensationPoint' // Para obtener el nombre del punto de dispensación
        ])
            ->whereIn('id', $purchaseRequestIds)
            ->get();

        // Agrupar los productos por product_id y sumar las cantidades
        $groupedProducts = [];

        foreach ($purchaseRequests as $purchaseRequest) {
            foreach ($purchaseRequest->productPurchaseRequest as $product) {
                if (!isset($groupedProducts[$product->product_id])) {
                    $groupedProducts[$product->product_id] = clone $product;
                    $groupedProducts[$product->product_id]->ammount = 0;
                    // Inicializar los campos adicionales
                    $groupedProducts[$product->product_id]->dispensation_point = null;
                    $groupedProducts[$product->product_id]->requested_by = null;
                    $groupedProducts[$product->product_id]->request_date = null;
                }
                $groupedProducts[$product->product_id]->ammount += $product->ammount;

                // Añadir información adicional al producto
                $groupedProducts[$product->product_id]->dispensation_point = $purchaseRequest->dispensationPoint->Nombre ?? null;
                $groupedProducts[$product->product_id]->requested_by = $purchaseRequest->user->person->full_name ?? null;
                $groupedProducts[$product->product_id]->request_date = $purchaseRequest->created_at ?? null;
            }
        }

        // Convertir el array a una colección
        $groupedProducts = collect($groupedProducts)->values();

        // Obtener los IDs de los productos agrupados
        $productIds = $groupedProducts->pluck('product_id')->toArray();

        // Obtener productos con sus variables y etiquetas asociadas
        [$productosConVariables, $variablesLabels] = $this->obtenerProductosConMismosCampos($productIds);

        // Crear un mapa de variables de productos
        $variablesMap = [];
        foreach ($productosConVariables as $producto) {
            $variablesMap[$producto['Id_Producto']] = $producto['variables'];
        }

        // Añadir el valor del impuesto y variables a cada producto
        foreach ($groupedProducts as $product) {
            $productModel = Product::with('impuesto')->find($product->product_id);
            if ($productModel && $productModel->impuesto) {
                $product->impuesto_valor = $productModel->impuesto->Valor;
            } else {
                $product->impuesto_valor = null;
            }

            // Añadir variables dentro de products
            if (isset($variablesMap[$product->product_id])) {
                $product->product->variables = $variablesMap[$product->product_id];
            } else {
                $product->product->variables = [];
            }

            // Asegurar que todos los productos tengan todos los labels
            foreach ($variablesLabels as $label) {
                if (!array_key_exists($label, $product->product->variables)) {
                    $product->product->variables[$label] = '';
                }
            }
        }

        return $this->success($groupedProducts);
    }

    private function obtenerProductosConMismosCampos($productosIds)
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

        $productosArray = [];
        foreach ($productos as $producto) {
            $productoArray = $producto->toArray();
            $productoArray['variables'] = $producto->variables;
            $productosArray[] = $productoArray;
        }

        // Asegurar que todos los productos tengan todos los labels
        foreach ($productosArray as &$producto) {
            foreach ($variablesLabels as $label) {
                if (!array_key_exists($label, $producto['variables'])) {
                    $producto['variables'][$label] = '';
                }
            }
        }

        return [$productosArray, array_unique($variablesLabels)];
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
            $products_delete = $request->products_delete;
            $data = $request->except('products', 'products_delete');
            $products = $request->products;
            $data['user_id'] = auth()->user()->person_id;
            if (!$request->id) {
                $data['code'] = generateConsecutive('purchase_requests');
            }
            $data['quantity_of_products'] = count($products);
            $data['dispensation_point_id'] = Person::find(auth()->user()->person_id)->dispensing_point_id;
            $actualData = PurchaseRequest::find($request->id);
            $purchaseRequest = PurchaseRequest::updateOrcreate(
                ['id' => $request->id],
                $data
            );
            if (count($products_delete) > 0) {
                foreach ($products_delete as $product) {
                    $productDelete = ProductPurchaseRequest::find($product);
                    $this->newActivity(
                        "Se eliminó el producto " . $productDelete->name,
                        'Edición',
                        $purchaseRequest->id
                    );
                    $productDelete->delete();
                }
            }
            foreach ($products as $product) {
                $productOld = $product['id'] ? ProductPurchaseRequest::find($product['id'])->toArray() : '';
                $productAdd = $purchaseRequest->productPurchaseRequest()->updateOrCreate(['id' => $product['id']], $product);
                if ($productAdd->wasRecentlyCreated) {
                    $this->newActivity(
                        "Se agregó el producto " . $productAdd->name,
                        'Edición',
                        $purchaseRequest->id
                    );
                } else if ($product['id']) {
                    if ($product['ammount'] !== $productOld['ammount']) {
                        $this->newActivity(
                            "Se modificó el producto " . $productAdd->name,
                            'Edición',
                            $purchaseRequest->id
                        );
                    }
                }
            }
            //actividad compra va aquí
            if ($purchaseRequest->wasRecentlyCreated) {
                $this->newActivity(
                    "Se creó la solicitud de compra " . $purchaseRequest->code,
                    'Creación',
                    $purchaseRequest->id
                );
            } else {
                foreach ($data as $key => $campo) {
                    if ($key != 'quantity_of_products' && $campo !== $actualData[$key]) {
                        $this->newActivity(
                            "Se editó la solicitud de compra " . $purchaseRequest->code,
                            'Edición',
                            $purchaseRequest->id
                        );
                    }
                }
            }

            if (!$request->id) {
                sumConsecutive('purchase_requests');
            }
            return $this->success('Creado con éxito');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PurchaseRequest  $purchaseRequest
     * @return \Illuminate\Http\JsonResponse
     */

    public function newActivity($details, $status, $id)
    {
        PurchaseRequestActivity::create(
            [
                'purchase_request_id' => $id,
                'user_id' => auth()->user()->person_id,
                'details' => $details,
                'date' => Carbon::now(),
                'status' => $status
            ]
        );
        return $this->success('ok');
    }
    public function show($id)
    {
        $resultado = PurchaseRequest::with('productPurchaseRequest', 'person', 'quotationPurchaseRequest', 'activity', 'dispensationPoint')->find($id);
        $variablesLabels = [];
        foreach ($resultado->productPurchaseRequest as $productPurchaseRequest) {
            if (isset($productPurchaseRequest->product)) {
                $variables = [];
                foreach ($productPurchaseRequest->product->variableProductsSinRecepcion as $variableProduct) {
                    $variables[$variableProduct->categoryVariable->label] = $variableProduct->valor;
                    $variablesLabels[] = $variableProduct->categoryVariable->label;
                }
                $productPurchaseRequest->variables = $variables;
            }
        }
        $resultado->variables = $variablesLabels;
        return $this->success($resultado);
    }

    //Funcion que guarda cotizaciones cargadas por producto
    public function saveQuotationPurchaseRequest(Request $request)
    {
        // dd($request);
        $items = $request->items;
        //dd($items);
        $code = generateConsecutive('quotation_purchase_requests');


        foreach ($items as $key => $value) {
            $value['code'] = $code . '-' . ($key + 1);
            $base64 = saveBase64File($value["file"], 'cotizaciones-solicitud-compra/', false, '.pdf');
            $value['file'] = URL::to('/') . '/api/file-view?path=' . $base64;
            QuotationPurchaseRequest::create($value);

            if ($value["product_purchase_request_id"]) {
                $product = ProductPurchaseRequest::find($value['product_purchase_request_id']);
                $product->update(['status' => 'Cotizaciones cargadas']);

                $this->newActivity(("Se cotizó el producto " . $product['name'] . " con número de cotización " . $value['code']), 'Cotización', $product->purchase_request_id);
            }

            if ($value["purchase_request_id"]) {
                $purchase = PurchaseRequest::find($value['purchase_request_id']);
                $products = $purchase->productPurchaseRequest()->get();

                foreach ($products as $product) {
                    $product->update(['status' => 'Cotizaciones cargadas']);
                }

                $this->newActivity(("Se realizó la cotización general número " . $value['code']), 'Cotización', $purchase->id);
            }
        }

        if ($value["product_purchase_request_id"]) {
            PurchaseRequest::find($product->purchase_request_id)->update(['status' => 'Cotizada']);
        } else {
            PurchaseRequest::find($value['purchase_request_id'])->update(['status' => 'Cotizada']);
        }

        sumConsecutive('quotation_purchase_requests');
        return $this->success('Cotización guardada con éxito');
    }



    public function getQuotationPurchaserequest($id, $value)
    {
        // esta validacion no esta funcionando
        if ($value == 'product') {
            $quotation = QuotationPurchaseRequest::with('productPurchaseRequest')
                ->where('product_purchase_request_id', $id)->with('thirdParty')->get();
        } else if ($value == 'purchase') {
            $quotation = QuotationPurchaseRequest::with('purchaseRequest')
                ->where('purchase_request_id', $id)->with('thirdParty')->get();
        } else {
            $quotation = '';
        }
        return $this->success($quotation);
    }

    public function saveQuotationApproved($id)
    {
        // actualizacion status quotations purchase request
        $quotationPurchase = QuotationPurchaseRequest::find($id);
        $quotationPurchase->update(['status' => 'Aprobada']);
        $quotationIds =
            QuotationPurchaseRequest::whereNotNull('product_purchase_request_id')
                ->where('product_purchase_request_id', $quotationPurchase->product_purchase_request_id)
                ->where('id', '<>', $id)
                ->pluck('id');
        //dd($quotationIds);
        $quotationsIdsGeneral =
            QuotationPurchaseRequest::whereNotNull('purchase_request_id')
                ->where('purchase_request_id', $quotationPurchase->purchase_request_id)
                ->where('id', '<>', $id)
                ->pluck('id');
        //dd($quotationsIdsGeneral);
        if (!$quotationIds->isEmpty()) {
            QuotationPurchaseRequest::whereIn('id', $quotationIds)->update(['status' => 'Rechazada']);
        }

        if (!$quotationsIdsGeneral->isEmpty()) {
            QuotationPurchaseRequest::whereIn('id', $quotationsIdsGeneral)->update(['status' => 'Rechazada']);
        }

        if ($quotationPurchase['product_purchase_request_id'] && !$quotationPurchase['purchase_request_id']) {
            $productPurchaseRequest = ProductPurchaseRequest::find($quotationPurchase->product_purchase_request_id);
            $productPurchaseRequest->update(['status' => 'Cotización Aprobada']);

            $this->newActivity("Se aprobó la cotización " . $quotationPurchase->code . " del producto " . $productPurchaseRequest->name, 'Aprobación', $productPurchaseRequest->purchase_request_id);

            $purchaseRequest = PurchaseRequest::find($productPurchaseRequest->purchase_request_id);
            $allProductsPurchaseRequestApproved = ProductPurchaseRequest::where('purchase_request_id', $purchaseRequest->id)
                ->where('status', '<>', 'Cotización Aprobada')  //valor diff de cotizacion aprobada
                ->count() == 0;
            if ($allProductsPurchaseRequestApproved) {
                $purchaseRequest->update(['status' => 'Aprobada']);
                $this->newActivity("Se aprobaron todas las cotizaciones de la solicitud " . $purchaseRequest->code, 'Aprobación', $purchaseRequest->id);
            }
        }
        //dd($quotationsIdsGeneral);
        if (!$quotationPurchase['product_purchase_request_id'] && $quotationPurchase['purchase_request_id']) {
            $purchaseRequest = PurchaseRequest::find($quotationPurchase->purchase_request_id);
            $purchaseRequest->update(['status' => 'Aprobada']);


            $productPurchaseRequest = ProductPurchaseRequest::where('purchase_request_id', $purchaseRequest->id);
            $productPurchaseRequest->update(['status' => 'Cotización Aprobada']);
            //dd($purchaseRequest);

            $this->newActivity("Se aprobó la cotización general " . $quotationPurchase->code, 'Aprobación', $purchaseRequest->id);
        }
        //dd($productPurchaseRequest);

        return $this->success('Operación existosa');
    }

}
