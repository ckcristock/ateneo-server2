<?php

//namespace Database\Seeders;

use App\Models\Product;
use App\Models\Unit;
use App\Models\VariableProduct;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class MedicamentosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $guion = Unit::create([
            'name' => 'Guion',
            'unit' => '-',
        ]);

        $percentage = Unit::create([
            'name' => 'Porcentaje',
            'unit' => '%',
        ]);

        $percentageVV = Unit::create([
            'name' => 'Porcentaje V/V',
            'unit' => '% (V/V)',
        ]);

        $percentageWV = Unit::create([
            'name' => 'Porcentaje W/V',
            'unit' => '% (W/V)',
        ]);

        $percentageWW = Unit::create([
            'name' => 'Porcentaje W/W',
            'unit' => '% (W/W)',
        ]);

        $percentagev_v = Unit::create([
            'name' => 'Porcentaje v/v',
            'unit' => '% v/v',
        ]);

        $percentagew_w = Unit::create([
            'name' => 'Porcentaje w/w',
            'unit' => '% w/w',
        ]);

        $percentagew_w2 = Unit::create([
            'name' => 'Porcentaje w/w2',
            'unit' => '%(w/w)',
        ]);

        $microgram = Unit::create([
            'name' => 'Microgramo',
            'unit' => 'µg',
        ]);

        $micromol = Unit::create([
            'name' => 'Micromol',
            'unit' => 'µmol',
        ]);

        $æg = Unit::create([
            'name' => 'æg',
            'unit' => 'æg',
        ]);

        $æmol = Unit::create([
            'name' => 'æmol',
            'unit' => 'æmol',
        ]);

        $AgU = Unit::create([
            'name' => 'Ag/U',
            'unit' => 'AgU',
        ]);

        $billionOrganisms = Unit::create([
            'name' => 'Billion Organisms',
            'unit' => 'billion organisms',
        ]);

        $mg = Unit::create([
            'name' => 'Miligramos',
            'unit' => 'mg',
        ]);

        $g = Unit::create([
            'name' => 'Gramos',
            'unit' => 'g',
        ]);

        $U_FIP = Unit::create([
            'name' => 'U FIP',
            'unit' => 'U FIP',
        ]);

        $U_I = Unit::create([
            'name' => 'U.I.',
            'unit' => 'U.I.',
        ]);

        $UI = Unit::create([
            'name' => 'UI',
            'unit' => 'UI',
        ]);

        $KUI = Unit::create([
            'name' => 'KUI',
            'unit' => 'KUI',
        ]);

        $kIU = Unit::create([
            'name' => 'kIU',
            'unit' => 'kIU',
        ]);

        $mcg = Unit::create([
            'name' => 'mcg',
            'unit' => 'mcg',
        ]);

        $mg_dot = Unit::create([
            'name' => 'mg.',
            'unit' => 'mg.',
        ]);

        $ug = Unit::create([
            'name' => 'ug',
            'unit' => 'ug',
        ]);

        $MBq = Unit::create([
            'name' => 'MBq',
            'unit' => 'MBq',
        ]);

        $IU = Unit::create([
            'name' => 'IU',
            'unit' => 'IU',
        ]);

        $ml = Unit::create([
            'name' => 'ml',
            'unit' => 'ml',
        ]);

        $UD = Unit::create([
            'name' => 'UD',
            'unit' => 'UD',
        ]);

        $MILLONES = Unit::create([
            'name' => 'MILLONES',
            'unit' => 'MILLONES',
        ]);

        $MU = Unit::create([
            'name' => 'MU',
            'unit' => 'MU',
        ]);

        $U = Unit::create([
            'name' => 'U',
            'unit' => 'U',
        ]);

        $Ul = Unit::create([
            'name' => 'Ul',
            'unit' => 'Ul',
        ]);

        $microgramos = Unit::create([
            'name' => 'microgramos',
            'unit' => 'microgramos',
        ]);

        $mL_dot = Unit::create([
            'name' => 'mL.',
            'unit' => 'mL.',
        ]);

        $LfU = Unit::create([
            'name' => 'LfU',
            'unit' => 'LfU',
        ]);

        $mg_titer = Unit::create([
            'name' => 'mg (titer)',
            'unit' => 'mg (titer)',
        ]);

        $g_l = Unit::create([
            'name' => 'g/l',
            'unit' => 'g/l',
        ]);

        $million_unit = Unit::create([
            'name' => 'million unit',
            'unit' => 'million unit',
        ]);

        $Millardos = Unit::create([
            'name' => 'Millardos',
            'unit' => 'Millardos',
        ]);

        $g_dot = Unit::create([
            'name' => 'g.',
            'unit' => 'g.',
        ]);

        $UI_CGB = Unit::create([
            'name' => 'UI CGB',
            'unit' => 'UI CGB',
        ]);

        $TCID50_dose = Unit::create([
            'name' => 'TCID50/dose',
            'unit' => 'TCID50/dose',
        ]);

        $million_IU = Unit::create([
            'name' => 'million IU',
            'unit' => 'million IU',
        ]);

        $mcg_dot = Unit::create([
            'name' => 'mcg.',
            'unit' => 'mcg.',
        ]);

        $UI_ml = Unit::create([
            'name' => 'UI/mL',
            'unit' => 'UI/mL',
        ]);

        $ELISA_unit = Unit::create([
            'name' => 'ELISA unit',
            'unit' => 'ELISA unit',
        ]);

        $DICC50 = Unit::create([
            'name' => 'DICC50',
            'unit' => 'DICC50',
        ]);

        $UFP = Unit::create([
            'name' => 'UFP',
            'unit' => 'UFP',
        ]);

        $MBq_dot = Unit::create([
            'name' => 'MBq.',
            'unit' => 'MBq.',
        ]);

        $unidades_ELISA = Unit::create([
            'name' => 'unidades ELISA',
            'unit' => 'unidades ELISA',
        ]);

        $CCID50 = Unit::create([
            'name' => 'CCID50',
            'unit' => 'CCID50',
        ]);

        $mEq_ml = Unit::create([
            'name' => 'mEq/ml',
            'unit' => 'mEq/ml',
        ]);

        $IU_ml = Unit::create([
            'name' => 'IU/ml',
            'unit' => 'IU/ml',
        ]);

        $KIU_ml = Unit::create([
            'name' => 'KIU/ml',
            'unit' => 'KIU/ml',
        ]);

        $qs = Unit::create([
            'name' => 'qs',
            'unit' => 'qs',
        ]);

        $PFU = Unit::create([
            'name' => 'PFU',
            'unit' => 'PFU',
        ]);

        $mg_ml = Unit::create([
            'name' => 'mg/ml',
            'unit' => 'mg/ml',
        ]);

        $kBq = Unit::create([
            'name' => 'kBq',
            'unit' => 'kBq',
        ]);

        $mmol = Unit::create([
            'name' => 'mmol',
            'unit' => 'mmol',
        ]);

        $mCi = Unit::create([
            'name' => 'mCi',
            'unit' => 'mCi',
        ]);

        $GBq_ml = Unit::create([
            'name' => 'GBq/ml',
            'unit' => 'GBq/ml',
        ]);

        $miligramos = Unit::create([
            'name' => 'miligramos',
            'unit' => 'miligramos',
        ]);

        $MUI = Unit::create([
            'name' => 'MUI',
            'unit' => 'MUI',
        ]);

        $Millon_de_UI = Unit::create([
            'name' => 'Millon de UI',
            'unit' => 'Millon de UI',
        ]);

        $GBq = Unit::create([
            'name' => 'GBq',
            'unit' => 'GBq',
        ]);

        $log_TCID50 = Unit::create([
            'name' => 'log TCID50',
            'unit' => 'log TCID50',
        ]);

        $log_UFP = Unit::create([
            'name' => 'log UFP',
            'unit' => 'log UFP',
        ]);

        $millones_IU = Unit::create([
            'name' => 'millones IU',
            'unit' => 'millones IU',
        ]);

        $mg_parche = Unit::create([
            'name' => 'mg/parche',
            'unit' => 'mg/parche',
        ]);

        $UFC = Unit::create([
            'name' => 'UFC',
            'unit' => 'UFC',
        ]);

        $million_organisms = Unit::create([
            'name' => 'million organisms',
            'unit' => 'million organisms',
        ]);

        $billionOrganisms = Unit::create([
            'name' => 'Billion Organisms',
            'unit' => 'billion organisms',
        ]);

        $percentage_vv = Unit::create([
            'name' => 'Porcentaje v/v',
            'unit' => '% v/v',
        ]);

        $percentage_ww = Unit::create([
            'name' => 'Porcentaje w/w',
            'unit' => '% w/w',
        ]);

        $millones_UI = Unit::create([
            'name' => 'Millones UI',
            'unit' => 'millones UI',
        ]);

        $VV = Unit::create([
            'name' => 'V/ V',
            'unit' => 'V/ V',
        ]);

        $mg_mL = Unit::create([
            'name' => 'mg /mL',
            'unit' => 'mg /mL',
        ]);

        $UI_mL = Unit::create([
            'name' => 'UI / mL',
            'unit' => 'UI / mL',
        ]);

        $UT = Unit::create([
            'name' => 'UT',
            'unit' => 'UT',
        ]);

        $Unidades = Unit::create([
            'name' => 'Unidades',
            'unit' => 'Unidades',
        ]);

        $g_ml = Unit::create([
            'name' => 'g/ml',
            'unit' => 'g/ml',
        ]);

        $UI_CGB = Unit::create([
            'name' => 'UI CGB',
            'unit' => 'UI CGB',
        ]);

        $log_10_UFP = Unit::create([
            'name' => 'log 10 UFP',
            'unit' => 'log 10 UFP',
        ]);

        $SU = Unit::create([
            'name' => 'SU',
            'unit' => 'SU',
        ]);

        $pH = Unit::create([
            'name' => 'pH',
            'unit' => 'pH',
        ]);

        $unidad = Unit::create([
            'name' => 'Unidad',
            'unit' => 'unidad',
        ]);

        $m3 = Unit::create([
            'name' => 'm3',
            'unit' => 'm3',
        ]);

        $chunksDir = database_path('seeds/json/output/');
        $numChunks = 96;
        for ($i = 0; $i <= $numChunks; $i++) {
            $jsonPath = $chunksDir . 'chunk_' . $i . '.json';
            $jsonData = File::get($jsonPath);
            $data = json_decode($jsonData, true);

            if ($data === null) {
                throw new Exception("Error al decodificar el archivo JSON: chunk_" . $i);
            }

            foreach ($data as $item) {
                $unit = Unit::where('unit', $item['unidadmedida'])->first();
                $subcategoryId = match ($item['SUBCATEGORIA']) {
                    'MEDICAMENTO CONTROL ESPECIAL' => 5,
                    'MEDICAMENTO ESTEECHO MARGEN TERAPEUTICO' => 9,
                    'GENERICO' => 3,
                    'COMERCIAL' => 10,
                    'MEDICAMENTO CONTROL ESPECIAL MONOPOLIO' => 4,
                    default => null,
                };
                if ($unit) {
                    $product = Product::create([
                        'Nombre_Comercial' => $item['Nombre'],
                        'Nombre_General' => $item['Nombre'],
                        'Cantidad_Minima' => $item['CANTIDAD_MINIMA_PARA_COMPRA'],
                        'Cantidad_Maxima' => $item['CANTIDAD_MAXIMA_PARA_COMPRA'],
                        'Unidad_Medida' => $unit->id,
                        'Id_Categoria' => 5,
                        'Id_Subcategoria' => $subcategoryId,
                        'Gravado' => 'no',
                        'impuesto_id' => 1,
                        'company_id' => 1,
                        'Estado' => 'activo',
                    ]);
                    VariableProduct::create([
                        'product_id' => $product->Id_Producto,
                        'category_variables_id' => 1,
                        'valor' => $item['COD_CUM'],
                    ]);
                    VariableProduct::create([
                        'product_id' => $product->Id_Producto,
                        'category_variables_id' => 2,
                        'valor' => $item['expediente'],
                    ]);
                    VariableProduct::create([
                        'product_id' => $product->Id_Producto,
                        'category_variables_id' => 11,
                        'valor' => $item['consecutivocum'],
                    ]);
                    VariableProduct::create([
                        'product_id' => $product->Id_Producto,
                        'category_variables_id' => 4,
                        'valor' => $item['Laboratorio'],
                    ]);
                    VariableProduct::create([
                        'product_id' => $product->Id_Producto,
                        'category_variables_id' => 5,
                        'valor' => $item['registrosanitario'],
                    ]);
                    VariableProduct::create([
                        'product_id' => $product->Id_Producto,
                        'category_variables_id' => 7,
                        'valor' => $item['fechaexpedicion'],
                    ]);
                    VariableProduct::create([
                        'product_id' => $product->Id_Producto,
                        'category_variables_id' => 8,
                        'valor' => $item['fechavencimiento'],
                    ]);
                    VariableProduct::create([
                        'product_id' => $product->Id_Producto,
                        'category_variables_id' => 9,
                        'valor' => $item['estadoregistro'],
                    ]);
                    VariableProduct::create([
                        'product_id' => $product->Id_Producto,
                        'category_variables_id' => 12,
                        'valor' => $item['cantidadcum'],
                    ]);
                    VariableProduct::create([
                        'product_id' => $product->Id_Producto,
                        'category_variables_id' => 13,
                        'valor' => $item['descripcioncomercial'],
                    ]);
                    VariableProduct::create([
                        'product_id' => $product->Id_Producto,
                        'category_variables_id' => 14,
                        'valor' => $item['estadocum'],
                    ]);
                    VariableProduct::create([
                        'product_id' => $product->Id_Producto,
                        'category_variables_id' => 15,
                        'valor' => $item['fechaactivo'],
                    ]);
                    VariableProduct::create([
                        'product_id' => $product->Id_Producto,
                        'category_variables_id' => 16,
                        'valor' => $item['fechainactivo'],
                    ]);
                    VariableProduct::create([
                        'product_id' => $product->Id_Producto,
                        'category_variables_id' => 17,
                        'valor' => $item['muestramedica'],
                    ]);
                    VariableProduct::create([
                        'product_id' => $product->Id_Producto,
                        'category_variables_id' => 18,
                        'valor' => $item['unidad'],
                    ]);
                    VariableProduct::create([
                        'product_id' => $product->Id_Producto,
                        'category_variables_id' => 19,
                        'valor' => $item['atc'],
                    ]);
                    VariableProduct::create([
                        'product_id' => $product->Id_Producto,
                        'category_variables_id' => 20,
                        'valor' => $item['descripcionatc'],
                    ]);
                    VariableProduct::create([
                        'product_id' => $product->Id_Producto,
                        'category_variables_id' => 21,
                        'valor' => $item['viaadministracion'],
                    ]);
                    VariableProduct::create([
                        'product_id' => $product->Id_Producto,
                        'category_variables_id' => 22,
                        'valor' => $item['concentracion'],
                    ]);
                    VariableProduct::create([
                        'product_id' => $product->Id_Producto,
                        'category_variables_id' => 23,
                        'valor' => $item['principioactivo'],
                    ]);
                    VariableProduct::create([
                        'product_id' => $product->Id_Producto,
                        'category_variables_id' => 26,
                        'valor' => $item['cantidad'],
                    ]);
                    VariableProduct::create([
                        'product_id' => $product->Id_Producto,
                        'category_variables_id' => 27,
                        'valor' => $item['unidadreferencia'],
                    ]);
                    VariableProduct::create([
                        'product_id' => $product->Id_Producto,
                        'category_variables_id' => 28,
                        'valor' => $item['formafarmaceutica'],
                    ]);
                    VariableProduct::create([
                        'product_id' => $product->Id_Producto,
                        'category_variables_id' => 29,
                        'valor' => $item['nombrerol'],
                    ]);
                    VariableProduct::create([
                        'product_id' => $product->Id_Producto,
                        'category_variables_id' => 30,
                        'valor' => $item['tiporol'],
                    ]);
                    VariableProduct::create([
                        'product_id' => $product->Id_Producto,
                        'category_variables_id' => 31,
                        'valor' => $item['modalidad'],
                    ]);
                    VariableProduct::create([
                        'product_id' => $product->Id_Producto,
                        'category_variables_id' => 32,
                        'valor' => $item['IUM'],
                    ]);
                    VariableProduct::create([
                        'product_id' => $product->Id_Producto,
                        'category_variables_id' => 33,
                        'valor' => $item['Grupo_Terapeutico'],
                    ]);
                    VariableProduct::create([
                        'product_id' => $product->Id_Producto,
                        'category_variables_id' => 36,
                        'valor' => $item['TIPO_DE_REGULACION_SI_O_NO'],
                    ]);
                    VariableProduct::create([
                        'product_id' => $product->Id_Producto,
                        'category_variables_id' => 37,
                        'valor' => $item['TIPO_PBS_PBS_NO_PBS'],
                    ]);
                    // MEDICAMENTOS_DE_CONTROL -> NO
                    // ESTRECHO_MARGEN_TERA -> NO
                    VariableProduct::create([
                        'product_id' => $product->Id_Producto,
                        'category_variables_id' => 41,
                        'valor' => $item['COMERCIAL_Y_O_GENERICO'],
                    ]);
                }
            }
        }
    }
}
