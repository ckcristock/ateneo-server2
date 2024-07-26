<?php

use App\Models\Product;
use Illuminate\Support\Facades\DB;

if (!function_exists('getLoteFechaVencimiento')) {
    function getLoteFechaVencimiento($productos)
    {
        $productosFinales = collect();

        foreach ($productos as $producto) {
            $productoInfo = Product::with('category')->find($producto->Id_Producto);
            $hasLote = $productoInfo->category->has_lote;
            $hasExpirationDate = $productoInfo->category->has_expiration_date;

            // Obtener los lotes y fechas de vencimiento de la tabla Inventario_Nuevo
            $inventarios = DB::table('Inventario_Nuevo')
                ->where('Id_Producto', $producto->Id_Producto)
                ->get(['Lote', 'Fecha_Vencimiento']);

            if ($hasLote || $hasExpirationDate) {
                $lotes = [];
                foreach ($inventarios as $inventario) {
                    $loteInfo = [];
                    if ($hasLote) {
                        $loteInfo['Lote'] = $inventario->Lote;
                    }
                    if ($hasExpirationDate) {
                        $loteInfo['Fecha_Vencimiento'] = $inventario->Fecha_Vencimiento;
                    }
                    $lotes[] = $loteInfo;
                }
                $producto->setAttribute('Lotes', $lotes);
            }

            $productosFinales->push($producto);
        }

        return $productosFinales;
    }


}



