<?php
Route::prefix('torneos')->namespace('Torneos')->group(function () {
    $controlador = 'TorneoDetalleController';

    Route::get('mostrar/{id}', "$controlador@mostrar");
    Route::post('comentarios', "$controlador@crearComentario");
    Route::patch('{id}/aceptar', "$controlador@aceptarDesafio");
    Route::get('ranking/{id}', "$controlador@ranking");
    Route::post('{id}/actualizar-puntuacion', "$controlador@actualizarPuntuacion");
    Route::delete('{id}', "$controlador@eliminarTorneo");
    Route::post('{id}/reportar-ausencia', "$controlador@reportarAusencia");


    //Rutas de otorgar puntos----------------------------------------------------
    $controller2 = "TorneosPuntosController";
    Route::get('{id}/get/asignar/puntos', "$controller2@getTorneo");
    Route::post('{id}/asignar/puntos', "$controller2@asignarPuntosTorneo");

    //Rutas de eventos especiales----------------------------------------------------
    Route::get('get/tribus', "$controller2@getTribus");
    Route::post('asignar/puntos/especiales', "$controller2@asignarPuntosEvento");
    
});