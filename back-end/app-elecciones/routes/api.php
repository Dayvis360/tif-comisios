<?php

use App\Http\Controllers\ProvinciaController;
use App\Http\Controllers\ListaController;
use App\Http\Controllers\CandidatoController;
use Illuminate\Support\Facades\Route;

// Listar provincias
Route::get('/provincias', [ProvinciaController::class, 'index']);

// Crear una nueva provincia
Route::post('/provincias', [ProvinciaController::class, 'store']);

// Listar listas
Route::get('/listas', [ListaController::class, 'index']);

// Crear una nueva lista
Route::post('/listas', [ListaController::class, 'store']);

// Listar candidatos
Route::get('/candidatos', [CandidatoController::class, 'index']);

// Crear un nuevo candidato
Route::post('/candidatos', [CandidatoController::class, 'store']);