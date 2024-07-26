<?php

use App\Http\Controllers\BankAccountsController;
use Illuminate\Support\Facades\Route;

Route::get('paginateBankAccount', [BankAccountsController::class, 'paginate']);
Route::apiResource('banksAccount', BankAccountsController::class);
