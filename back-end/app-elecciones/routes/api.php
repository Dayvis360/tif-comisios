<?php

use App\Http\Controllers\ProvinciaController;
use Illuminate\Support\Facades\Route;

// Listar provincias
Route::get('/provincias', [ProvinciaController::class, 'index']);

// Crear una nueva provincia
Route::post('/provincias', [ProvinciaController::class, 'store']);