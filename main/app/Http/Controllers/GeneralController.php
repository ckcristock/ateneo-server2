<?php

namespace App\Http\Controllers;

use App\Http\Services\consulta;
use App\Models\DiarioTurnoFijo;
use App\Models\DiarioTurnoRotativo;
use App\Models\Person;
use App\Models\RotatingTurn;
use App\Models\RotatingTurnHour;
use App\Models\WorkContract;
use Carbon\Carbon;
use App\Http\Services\comprobantes\ObtenerProximoConsecutivo;
use App\Models\ActividadProducto;
use App\Models\Product;
use App\Models\Unit;
use App\Models\VariableProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class GeneralController extends Controller
{
    private function getCompany()
    {
        return Person::find(Auth()->user()->person_id)->company_worked_id;
    }

    public function pruebas()
    {
        /* Alert::create([
            'person_id' => 1,
            'user_id' => 1,
            'modal' => 0,
            'icon' => 'fas fa-file-contract',
            'type' => 'Finalización de contrato',
            'description' => 'Tu contrato finalizará el día '
        ]); */
        //event(new NewNotification('hola2'));
        $registrosFijosSinSalida = DiarioTurnoFijo::whereNull('leave_time_two')->get();
        $registrosRotativosSinSalida = DiarioTurnoRotativo::whereNull('leave_time_one')->get();
        foreach ($registrosRotativosSinSalida as $registro) {
            $personId = $registro->person_id;
            $fecha = $registro->date;

            $turno = RotatingTurnHour::where('person_id', $personId)
                ->where('date', $fecha)
                ->first();

            if ($turno) {
                $horaSalida = RotatingTurn::find($turno->rotating_turn_id)->leave_time;
                $horaEntrada = $registro->entry_time_one;
                $leaveDate = $horaSalida < $horaEntrada ? Carbon::parse($fecha)->addDay() : $fecha;
                $registro->leave_time_one = $horaSalida;
                $registro->leave_date = $leaveDate;
                $registro->save();
            }
        }
        foreach ($registrosFijosSinSalida as $registro) {
            $personId = $registro->person_id;
            $fecha = $registro->date;
            $fechaCarbon = Carbon::parse($fecha);
            $nombreDia = ucfirst($fechaCarbon->locale('es')->isoFormat('dddd'));
            $turno = WorkContract::where('person_id', $personId)->where('liquidated', 0)
                ->with([
                    'fixedTurn.horariosTurnoFijo' => function ($query) use ($nombreDia) {
                        $query->where('day', '=', $nombreDia);
                    }
                ])
                ->first();

            if ($turno && $turno->fixedTurn) {
                $horaSalida = $turno->fixedTurn->horariosTurnoFijo->first()->leave_time_two;
                $horaSalidaAlmuerzo = $turno->fixedTurn->horariosTurnoFijo->first()->leave_time_one;
                $horaEntradaAlmuerzo = $turno->fixedTurn->horariosTurnoFijo->first()->entry_time_two;
                $registro->leave_time_two = $horaSalida;
                $registro->leave_time_one = $horaSalidaAlmuerzo;
                $registro->entry_time_two = $horaEntradaAlmuerzo;
                $registro->save();
            }
        }
    }

    public function listaGenerales()
    {
        $mod = (isset($_REQUEST['modulo']) ? $_REQUEST['modulo'] : '');

        $condicion = '';

        if ($mod === 'Cliente') {
            $condicion = " WHERE Estado != 'Inactivo'";
        } elseif ($mod === 'Resolucion') {
            $condicion = "  ORDER BY Id_Resolucion DESC";
        }

        $query = 'SELECT * FROM ' . $mod . $condicion;
        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->setTipo('Multiple');
        $resultado = $oCon->getData();
        unset($oCon);
        return response()->json($resultado);
    }

    public function detalle()
    {
        $mod = (isset($_REQUEST['modulo']) ? $_REQUEST['modulo'] : '');
        $id = (isset($_REQUEST['id']) ? $_REQUEST['id'] : '');
        /*
        $oItem = new complex($mod,"Id_".$mod,$id);
        $detalle= $oItem->getData();
        unset($oItem);*/
        $query = 'SELECT D.*
        FROM ' . $mod . ' D
        WHERE D.Id_' . $mod . ' = ' . $id;
        $oCon = new consulta();
        $oCon->setQuery($query);
        $detalle = $oCon->getData();
        unset($oCon);
        //var_dump ($detalle);
        return response()->json($detalle, JSON_UNESCAPED_UNICODE);
    }


    public function getCodigo(Request $request)
    {

        $tipo = $request->input('Tipo');

        if ($tipo === null) {
            return response()->json(['error' => 'Parámetro Tipo no encontrado'], 400);
        }

        $company_id = $this->getCompany();

        $fecha = $request->input('Fecha', null);
        $mes = $fecha ? date('m', strtotime($fecha)) : date('m');
        $anio = $fecha ? date('Y', strtotime($fecha)) : date('Y');


        $consecutivo = ObtenerProximoConsecutivo::obtener($tipo, $company_id, $mes, $anio);

        return response()->json([
            "consecutivo" => $consecutivo
        ]);
    }

    public function actualizarMedicamentos2()
    {
        try {
            $variableProducts = VariableProduct::where('category_variables_id', 1)->get();
            foreach ($variableProducts as $variableProduct) {
                Product::where('Id_Producto', $variableProduct->product_id)->update(['Referencia' => $variableProduct->valor]);
            }

            $products = Product::all();
            foreach ($products as $product) {
                ActividadProducto::create([
                    'Id_Producto' => $product->Id_Producto,
                    'Person_Id' => 1,
                    'Detalles' => 'Producto creado de manera masiva a partir del excel de productos.',
                    'Fecha' => Carbon::now()
                ]);

                $variables = VariableProduct::where('product_id', $product->Id_Producto)
                    ->whereIn('category_variables_id', [23, 28, 22, 26])
                    ->get()
                    ->keyBy('category_variables_id');

                $unidadMedida = Unit::find($product->Unidad_Medida);

                $nombreGeneral = $variables[23]->valor . ' ' . $variables[28]->valor . ' ' . $variables[22]->valor . ' ' . $variables[26]->valor . ' ' . $unidadMedida->name;

                $product->update(['Nombre_General' => $nombreGeneral]);
            }

            return 'ok';
        } catch (\Throwable $th) {
            return $th->getMessage();
        }

    }

    public function actualizarMedicamentos3()
    {
        try {
            $perPage = 1000;

            $pages = VariableProduct::where('category_variables_id', 1)->paginate($perPage);

            while ($pages->count() > 0) {
                foreach ($pages as $variableProduct) {
                    Product::where('Id_Producto', $variableProduct->product_id)->update(['Referencia' => $variableProduct->valor]);
                }
                $pages = $pages->nextPageUrl() ? $pages->nextPage($perPage) : collect();
            }

            $pages = Product::paginate($perPage);

            while ($pages->count() > 0) {
                foreach ($pages as $product) {
                    ActividadProducto::create([
                        'Id_Producto' => $product->Id_Producto,
                        'Person_Id' => 1,
                        'Detalles' => 'Producto creado de manera masiva a partir del excel de productos.',
                        'Fecha' => Carbon::now()
                    ]);

                    $variables = VariableProduct::where('product_id', $product->Id_Producto)
                        ->whereIn('category_variables_id', [23, 28, 22, 26])
                        ->get()
                        ->keyBy('category_variables_id');

                    $unidadMedida = Unit::find($product->Unidad_Medida);

                    $nombreGeneral = $variables[23]->valor . ' ' . $variables[28]->valor . ' ' . $variables[22]->valor . ' ' . $variables[26]->valor . ' ' . $unidadMedida->name;

                    $product->update(['Nombre_General' => $nombreGeneral]);
                }

                $pages = $pages->nextPageUrl() ? $pages->nextPage($perPage) : collect();
            }

            return 'ok';
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }

    public function actualizarMedicamentos()
    {
        try {
            $perPage = 1000;

            $pagesCount = ceil(VariableProduct::where('category_variables_id', 1)->count() / $perPage);

            for ($page = 1; $page <= $pagesCount; $page++) {
                $variableProducts = VariableProduct::where('category_variables_id', 1)->paginate($perPage, ['*'], 'page', $page);

                foreach ($variableProducts as $variableProduct) {
                    Product::where('Id_Producto', $variableProduct->product_id)->update(['Referencia' => $variableProduct->valor]);
                }
            }

            $pagesCount = ceil(Product::count() / $perPage);

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
                    echo $nombreGeneral;

                    $product->update(['Nombre_General' => $nombreGeneral]);
                }
                ActividadProducto::insert($actividades);
            }

            return 'ok';
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }


}
