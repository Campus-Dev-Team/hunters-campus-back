<?php

use App\Http\Controllers\Torneos\TorneoDetalleController;
use App\Http\Controllers\Torneos\TorneosPuntosController;

Route::group(['prefix' => 'torneos'], function () {
    // Rutas de TorneoDetalleController
    Route::get('mostrar/{id}', [TorneoDetalleController::class, 'mostrar']);
    Route::post('comentarios', [TorneoDetalleController::class, 'crearComentario']);
    Route::patch('{id}/aceptar', [TorneoDetalleController::class, 'aceptarDesafio']);
    Route::get('ranking/{id}', [TorneoDetalleController::class, 'ranking']);
    Route::post('{id}/actualizar-puntuacion', [TorneoDetalleController::class, 'actualizarPuntuacion']);
    Route::delete('{id}', [TorneoDetalleController::class, 'eliminarTorneo']);
    Route::post('{id}/reportar-ausencia', [TorneoDetalleController::class, 'reportarAusencia']);

    // Rutas de TorneosPuntosController
    Route::get('{id}/get/asignar/puntos', [TorneosPuntosController::class, 'getTorneo']);
    Route::post('{id}/asignar/puntos', [TorneosPuntosController::class, 'asignarPuntosTorneo']);
    Route::get('get/tribus', [TorneosPuntosController::class, 'getTribus']);
    Route::post('asignar/puntos/especiales', [TorneosPuntosController::class, 'asignarPuntosEvento']);
});