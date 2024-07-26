<?php

namespace App\Http\Controllers;

use App\Models\AccountConfiguration;
use App\Models\CategoriaNueva;
use App\Models\Category;
use App\Models\Person;
use App\Models\Product;
use App\Models\Subcategory;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AccountConfigurationController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $configurations = AccountConfiguration::all();

        return response()->json($configurations);
    }

    private function getModel($configurableEntityType)
    {
        $model = match ($configurableEntityType) {
            'category' => CategoriaNueva::class,
            'subcategory' => Subcategory::class,
            'product' => Product::class,
            default => null
        };
        return $model;
    }

    public function paginate(Request $request)
    {
        $configurableEntityType = $request->input('configurable_entity_type');
        $nameFilter = $request->input('name');

        $model = $this->getModel($configurableEntityType);
        $companyId = getCompanyWorkedId();

        $configuration = AccountConfiguration::with(
            'configurable',
            'retentionType',
            'reteivaPurchaseAccount',
            'incomeAccount',
            'inventoryAccount',
            'expenseAccount',
            'costAccount',
            'entryAccount',
            'saleIvaAccount',
            'purchaseIvaAccount',
            'saleDiscountAccount',
            'purchaseDiscountAccount',
            'retefuenteSaleAccount',
            'retefuentePurchaseAccount',
            'reteicaSaleAccount',
            'reteicaPurchaseAccount',
            'reteivaSaleAccount',
            'reteivaPurchaseAccount'
        )
            ->where('configurable_type', $model)
            ->where(function ($query) use ($companyId) {
                $query->whereHasMorph('configurable', [CategoriaNueva::class], function ($subQuery) use ($companyId) {
                    $subQuery->where('company_id', $companyId);
                });
                $query->orWhereHasMorph('configurable', [Subcategory::class, Product::class], function ($subQuery) use ($companyId) {
                    $subQuery->whereHas('category', function ($q) use ($companyId) {
                        $q->where('company_id', $companyId);
                    });
                });
            })
            ->where(function ($query) use ($nameFilter) {
                $query->whereHasMorph('configurable', [CategoriaNueva::class, Subcategory::class], function ($subQuery) use ($nameFilter) {
                    if (!empty ($nameFilter)) {
                        $subQuery->where('Nombre', 'like', "%$nameFilter%");
                    }
                });
                $query->orWhereHasMorph('configurable', [Product::class], function ($subQuery) use ($nameFilter) {
                    if (!empty ($nameFilter)) {
                        $subQuery->where('Nombre_Comercial', 'like', "%$nameFilter%");
                    }
                });

            })
            ->paginate(request()->get('pageSize', 10), ['*'], 'page', request()->get('page', 1));
        return $this->success($configuration);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $configurableEntityType = $request->input('configurable_entity_type');

            $data = $request->all();

            $model = $this->getModel($configurableEntityType);

            if (!$model) {
                return $this->error('Tipo de entidad no soportada', 400);
            }

            $data['configurable_type'] = $model;

            $configuration = AccountConfiguration::findOrFail($id);

            $configuration->update($data);

            return $this->success('Configuración actualizada correctamente');
        } catch (\Throwable $th) {

            return $this->error('Error al actualizar la configuración', 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
