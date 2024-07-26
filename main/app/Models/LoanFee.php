<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanFee extends Model
{
	protected $fillable = [
		"loan_id",
		"number",
		"amortization",
		"interest",
		"value",
		"outstanding_balance",
		"date",
		'payment_date',
		'state'
	];
}
