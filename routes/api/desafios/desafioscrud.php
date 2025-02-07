<?php
Route::prefix('desafios')->namespace('Desafios')->group(function () {
    $controlador = 'DesafiosCrudController';

    Route::get('mostrar/{id}', "$controlador@mostrar");
    Route::post('comentarios', "$controlador@crearComentario");
    Route::patch('{id}/aceptar', "$controlador@aceptarDesafio");
    Route::delete('{id}', "$controlador@eliminarDesafio");
});