<?php

use App\Models\ActividadProducto;
use App\Models\Product;
use App\Models\Unit;
use App\Models\VariableProduct;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class UpdateMedicamentosSeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $perPage = 1000;

        $pagesCount = ceil(VariableProduct::where('category_variables_id', 1)->count() / $perPage);
/*
        for ($page = 1; $page <= $pagesCount; $page++) {
            $variableProducts = VariableProduct::where('category_variables_id', 1)->paginate($perPage, ['*'], 'page', $page);

            foreach ($variableProducts as $variableProduct) {
                Product::where('Id_Producto', $variableProduct->product_id)->update(['Referencia' => $variableProduct->valor]);
            }
        } */

        for ($page = 1; $page <= $pagesCount; $page++) {
            $variableProducts = VariableProduct::where('category_variables_id', 13)->paginate($perPage, ['*'], 'page', $page);

            foreach ($variableProducts as $variableProduct) {
                Product::where('Id_Producto', $variableProduct->product_id)->update(['Unidad_Empaque' => $variableProduct->valor]);
            }
        }

        /* $pagesCount = ceil(Product::count() / $perPage);

        for ($page = 1; $page <= $pagesCount; $page++) {
            $products = Product::with([
                'variablesProducts' => function ($query) {
                    $query->whereIn('category_variables_id', [23, 28, 22, 26]);
                },
                'unit'
            ])->paginate($perPage, ['*'], 'page', $page);
            $actividades = [];
            foreach ($products as $product) {
                $actividades[] = [
                    'Id_Producto' => $product->Id_Producto,
                    'Person_Id' => 1,
                    'Detalles' => 'Producto creado de manera masiva a partir del excel de productos.',
                    'Fecha' => Carbon::now(),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ];

                $variables = $product->variablesProducts->keyBy('category_variables_id');
                $unidadMedida = $product->unit;

                $nombreGeneral = $variables[23]->valor . ' ' . $variables[28]->valor . ' ' . $variables[22]->valor . ' ' . $variables[26]->valor . ' ' . $unidadMedida->name;

                $product->update(['Nombre_General' => $nombreGeneral]);
            }
            ActividadProducto::insert($actividades);
        } */
    }
}
