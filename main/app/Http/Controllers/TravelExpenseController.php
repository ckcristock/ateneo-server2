<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Person;
use App\Models\TravelExpense;
use App\Models\TravelExpenseAirTransport;
use App\Models\TravelExpenseFeeding;
use App\Models\TravelExpenseHotel;
use App\Models\TravelExpenseTaxiCity;
use App\Models\TravelExpenseTransport;
use App\Services\TravelExpenseService;
use App\Traits\ApiResponser;
use Barryvdh\DomPDF\Facade\Pdf;

use Illuminate\Http\Request;

class TravelExpenseController extends Controller
{
	use ApiResponser;

	private function getCompany()
	{
		return Person::find(Auth()->user()->person_id)->company_worked_id;
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function index()
	{
		//
		return $this->success(
			TravelExpenseService::paginate($this->getCompany())
		);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function store(Request $request)
	{
		$totals = $request->except([
			"hospedaje",
			"taxi",
			"feeding",
			"transporte",
			"travel",
		]);
		$hotels = request()->get("hospedaje");
		$taxis = request()->get("taxi");
		$feedings = request()->get("feeding");
		$transports = request()->get("transporte");
		$airTransports = request()->get("air");
		$travelExpense = request()->get("travel");
		$travelExpense["user_id"] = auth()->user()->id;

		$travelExpense = array_merge($travelExpense, $totals);
		try {
			/* $TravelExpenseDB = TravelExpense::updateOrCreate(['id' => $request()->get('id')], $travelExpense); */
			$TravelExpenseDB = TravelExpense::create($travelExpense);
			foreach ($hotels as $hotel) {
				$hotel["travel_expense_id"] = $TravelExpenseDB->id;
				TravelExpenseHotel::create($hotel);
			}
			foreach ($taxis as $taxi) {
				$taxi["travel_expense_id"] = $TravelExpenseDB->id;
				TravelExpenseTaxiCity::create($taxi);
			}
			foreach ($feedings as $feeding) {
				$feeding["travel_expense_id"] = $TravelExpenseDB->id;
				TravelExpenseFeeding::create($feeding);
			}
			foreach ($transports as $transport) {
				$transport["travel_expense_id"] = $TravelExpenseDB->id;
				TravelExpenseTransport::create($transport);
			}
			foreach ($airTransports as $airTransport) {
				$airTransport["travel_expense_id"] = $TravelExpenseDB->id;
				TravelExpenseAirTransport::create($airTransport);
			}

			return $this->success("guardado con éxito");
		} catch (\Throwable $th) {
			return $this->errorResponse($th->getMessage(), 500);
		}
	}

	public function approve(Request $request, $id)
	{
		try {
			$approve = TravelExpense::find($id);
			$approve->update([
				'state' => $request->state,
				'approve_user_id' => auth()->user()->id
			]);
			return $this->success("Aprobado con éxito");
		} catch (\Throwable $th) {
			return $this->errorResponse($th->getMessage(), 500);
		}
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function show($id)
	{
		//
		return $this->success(
			TravelExpenseService::show($id)
		);
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

		$totals = $request->except([
			"hospedaje",
			"taxi",
			"feeding",
			"transporte",
			"travel",
		]);
		$hotels = request()->get("hospedaje");
		$taxis = request()->get("taxi");
		$feedings = request()->get("feeding");
		$transports = request()->get("transporte");
		$airTransports = request()->get("air");
		$travelExpense = request()->get("travel");

		$travelExpense = array_merge($travelExpense, $totals);
		try {
			TravelExpense::find($id)->update($travelExpense);
			TravelExpenseHotel::where("travel_expense_id", $id)->delete();
			TravelExpenseTaxiCity::where("travel_expense_id", $id)->delete();
			TravelExpenseFeeding::where("travel_expense_id", $id)->delete();
			TravelExpenseTransport::where("travel_expense_id", $id)->delete();
			TravelExpenseAirTransport::where("travel_expense_id", $id)->delete();
			foreach ($hotels as $hotel) {
				$hotel["travel_expense_id"] = $id;
				TravelExpenseHotel::create($hotel);
			}
			foreach ($taxis as $taxi) {
				$taxi["travel_expense_id"] = $id;
				TravelExpenseTaxiCity::create($taxi);
			}
			foreach ($feedings as $feeding) {
				$feeding["travel_expense_id"] = $id;
				TravelExpenseFeeding::create($feeding);
			}
			foreach ($transports as $transport) {
				$transport["travel_expense_id"] = $id;
				TravelExpenseTransport::create($transport);
			}
			foreach ($airTransports as $airTransport) {
				$airTransport["travel_expense_id"] = $id;
				TravelExpenseAirTransport::create($airTransport);
			}

			return $this->success("guardado con éxito");
		} catch (\Throwable $th) {
			return $this->errorResponse($th->getMessage() . ' ' . $th->getLine(), 500);
		}
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 *
	 */
	public function pdf($id, $company_id)
	{
		//
		$data = TravelExpenseService::show($id);
		$company = Company::where('id', $company_id)->first();
		//dd($request->all());
		$pdf = PDF::loadView('pdf.travel_expense', ['data' => $data, 'company' => $company]);
		return $pdf->download('travel_expense.pdf');
	}
}
