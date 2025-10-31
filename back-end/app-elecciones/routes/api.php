<?php

use App\Http\Controllers\ProvinciaController;
use App\Http\Controllers\ListaController;
use App\Http\Controllers\CandidatoController;
use App\Http\Controllers\MesaController;
use App\Http\Controllers\TelegramaController;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;

//hola mundo
Route::get('/hola', function () {
    return response()->json(['mensaje' => 'Hola mundo desde el backend']);
});

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

// Listar mesas
Route::get('/mesas', [MesaController::class, 'index']);

// Crear una nueva mesa
Route::post('/mesas', [MesaController::class, 'store']);

// Listar telegramas
Route::get('/telegramas', [TelegramaController::class, 'index']);

// Crear un nuevo telegrama
Route::post('/telegramas', [TelegramaController::class, 'store']);