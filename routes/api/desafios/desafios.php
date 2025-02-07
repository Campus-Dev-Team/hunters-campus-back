<?php
Route::prefix('desafios')->namespace('Desafios')->group(function () {
    $controlador = 'DesafiosController';

    Route::get('disponibles', "$controlador@getDesafios");
    Route::get('programados', "$controlador@getDesafios");
    Route::get('finalizados', "$controlador@getDesafios");
    Route::get('especiales', "$controlador@getDesafiosEspeciales");
    Route::get('otros', "$controlador@getOtrosDesafios");
    Route::get('info_torneo/{id}', "$controlador@getConsultarTorneo");
    Route::post('crear_torneo', "$controlador@postCrearTorneo");
    Route::put('{id}/editar_torneo', "$controlador@putEditarTorneo");
    
});

