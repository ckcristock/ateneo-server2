<?php

namespace App\Http\Controllers;

use App\Models\InventaryDotation;
use App\Models\Person;
use App\Models\ProductDotationType;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class ProductDotationTypeController extends Controller
{
    use ApiResponser;

    private function getCompany()
    {
        return Person::find(Auth()->user()->person_id)->company_worked_id;
    }

    public function index()
    {
        return $this->success(
            ProductDotationType::where('company_id', $this->getCompany())
                ->get()
        );
    }

    public function store(Request $req)
    {
        try {
            $data = $req->all();
            $data['company_id'] = $this->getCompany();
            ProductDotationType::create($data);
            return $this->success('Creado exitoso');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            $inventary = InventaryDotation::where('product_dotation_type_id', $id);
            if ($inventary->exists()) {
                return $this->error('No se puede eliminar la categoría porque hay elementos del inventario que pertenecen a ella.', 409);
            }
            ProductDotationType::destroy($id);
            return $this->success('Categoría eliminada con éxito');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 500);
        }
    }
}
