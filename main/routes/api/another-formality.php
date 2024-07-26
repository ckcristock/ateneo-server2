<?php

use App\Http\Controllers\AnotherFormality;
use Illuminate\Support\Facades\Route;

Route::post("another-formality", [AnotherFormality::class, "store"]);
