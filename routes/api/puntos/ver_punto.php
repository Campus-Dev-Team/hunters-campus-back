<?php

use App\Http\Controllers\Puntos\VerPuntoController;

Route::group(['prefix' => 'puntos/ver'], function() {
    Route::get('datos/{idPunto}', [VerPuntoController::class, 'datos']);
});