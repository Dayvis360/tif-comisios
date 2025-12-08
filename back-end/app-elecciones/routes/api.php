<?php

use App\Http\Controllers\ProvinciaController;
use App\Http\Controllers\ListaController;
use App\Http\Controllers\CandidatoController;
use App\Http\Controllers\MesaController;
use App\Http\Controllers\TelegramaController;
use App\Http\Controllers\DHontController;
use App\Http\Controllers\ResultadoController;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;

//hola mundo
Route::get('/hola', function () {
    return response()->json(['mensaje' => 'Hola mundo desde el backend']);
});

//PROVINCIAS
Route::get('/provincias', [ProvinciaController::class, 'index']);
Route::get('/provincias/{id}', [ProvinciaController::class, 'show']);
Route::post('/provincias', [ProvinciaController::class, 'store']);
Route::put('/provincias/{id}', [ProvinciaController::class, 'update']);
Route::delete('/provincias/{id}', [ProvinciaController::class, 'destroy']);

//  MESAS 
Route::get('/mesas', [MesaController::class, 'index']);
Route::get('/mesas/{id}', [MesaController::class, 'show']);
Route::post('/mesas', [MesaController::class, 'store']);
Route::put('/mesas/{id}', [MesaController::class, 'update']);
Route::delete('/mesas/{id}', [MesaController::class, 'destroy']);

//  LISTAS 
Route::get('/listas', [ListaController::class, 'index']);
Route::get('/listas/{id}', [ListaController::class, 'show']);
Route::post('/listas', [ListaController::class, 'store']);
Route::put('/listas/{id}', [ListaController::class, 'update']);
Route::delete('/listas/{id}', [ListaController::class, 'destroy']);

//  CANDIDATOS 
Route::get('/candidatos', [CandidatoController::class, 'index']);
Route::get('/candidatos/{id}', [CandidatoController::class, 'show']);
Route::post('/candidatos', [CandidatoController::class, 'store']);
Route::put('/candidatos/{id}', [CandidatoController::class, 'update']);
Route::delete('/candidatos/{id}', [CandidatoController::class, 'destroy']);

//  TELEGRAMAS 
Route::get('/telegramas', [TelegramaController::class, 'index']); // Soporta filtros: ?provincia_id=1&cargo=DIPUTADOS&lista_id=2&mesa_desde=1&mesa_hasta=10
Route::get('/telegramas/{id}', [TelegramaController::class, 'show']);
Route::post('/telegramas', [TelegramaController::class, 'store']);
Route::post('/telegramas/importar', [TelegramaController::class, 'importar']); // Importar CSV/JSON
Route::put('/telegramas/{id}', [TelegramaController::class, 'update']);
Route::delete('/telegramas/{id}', [TelegramaController::class, 'destroy']);

//  MÉTODO D'HONT 
// Calcular reparto de bancas por provincia
Route::get('/dhont/provincia/{provinciaId}', [DHontController::class, 'calcularPorProvincia']);

// Calcular reparto de bancas para todas las provincias
Route::get('/dhont/todas', [DHontController::class, 'calcularTodasProvincias']);

// Obtener resumen completo de resultados electorales
Route::get('/dhont/resumen', [DHontController::class, 'obtenerResumen']);

//  RESULTADOS Y ESTADÍSTICAS 
// Resultados nacionales (participación, totales por lista)
Route::get('/resultados/nacional', [ResultadoController::class, 'nacional']);

// Ranking nacional por cargo (DIPUTADOS o SENADORES)
Route::get('/resultados/ranking/{cargo}', [ResultadoController::class, 'ranking']);

// Estadísticas completas de una provincia
Route::get('/resultados/provincia/{id}/estadisticas', [ResultadoController::class, 'estadisticasProvincia']);

// Resumen de todas las provincias
Route::get('/resultados/provincias', [ResultadoController::class, 'resumenProvincias']);

//  EXPORTACIÓN DE RESULTADOS 
// Exportar resultados de una provincia (CSV o JSON)
Route::get('/resultados/provincia/{id}/exportar', [ResultadoController::class, 'exportarProvincia']);

// Exportar resultados nacionales (CSV o JSON)
Route::get('/resultados/nacional/exportar', [ResultadoController::class, 'exportarNacional']);

// Exportar resumen de provincias (CSV o JSON)
Route::get('/resultados/provincias/exportar', [ResultadoController::class, 'exportarResumenProvincias']);