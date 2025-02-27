<?php

use App\Http\Controllers\Desafios\DesafiosCrudController;

Route::group(['prefix' => 'desafios'], function () {
    Route::get('mostrar/{id}', [DesafiosCrudController::class, 'mostrar']);
    Route::post('comentarios', [DesafiosCrudController::class, 'crearComentario']);
    Route::patch('{id}/aceptar', [DesafiosCrudController::class, 'aceptarDesafio']);
    Route::delete('{id}', [DesafiosCrudController::class, 'eliminarDesafio']);
});