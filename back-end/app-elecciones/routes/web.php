<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EleccionController;

Route::get('/', function () {
    return view('welcome');
});

// Ruta para crear registros de elección
Route::post('/elecciones/crear', [EleccionController::class, 'crearRegistros']);
