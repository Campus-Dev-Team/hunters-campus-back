<?php

use App\Http\Controllers\Desafios\DesafiosController;

Route::group(['prefix' => 'desafios'], function () {
    Route::get('disponibles', [DesafiosController::class, 'getDesafios']);
    Route::get('programados', [DesafiosController::class, 'getDesafios']);
    Route::get('finalizados', [DesafiosController::class, 'getDesafios']);
    Route::get('especiales', [DesafiosController::class, 'getDesafiosEspeciales']);
    Route::get('otros', [DesafiosController::class, 'getOtrosDesafios']);
    Route::get('info_torneo/{id}', [DesafiosController::class, 'getConsultarTorneo']);
    Route::post('crear_torneo', [DesafiosController::class, 'postCrearTorneo']);
    Route::put('{id}/editar_torneo', [DesafiosController::class, 'putEditarTorneo']);
});

