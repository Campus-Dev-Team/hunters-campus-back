<?php

use App\Http\Controllers\Retos\GanadoresRetosController;

Route::group(['prefix' => 'retos'], function () {
    Route::get('datos/{idReto}/participantes', [GanadoresRetosController::class, 'getParticipantesReto']);
    Route::put('{id_reto}/ganador/finalizar', [GanadoresRetosController::class, 'setGanadorReto']);
});