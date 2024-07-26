<?php
if (!function_exists('getVariablesProductos')) {
    function getVariablesProductos($productos)
    {
        $variablesLabels = [];
        foreach ($productos as $producto) {
            $variables = [];
            foreach ($producto->variableProductsSinRecepcion as $variableProduct) {
                $variables[$variableProduct->categoryVariable->label] = $variableProduct->valor;
                $variablesLabels[] = $variableProduct->categoryVariable->label;
            }
            $producto->variables = $variables;
        }

        $collection = collect($variablesLabels);
        $variablesLabels = $collection->unique();

        return [$productos, $variablesLabels];
    }
}
