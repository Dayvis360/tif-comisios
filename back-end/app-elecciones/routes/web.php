<?php

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MesaController;
Route::get('/', function () {
    return view('welcome');
});


