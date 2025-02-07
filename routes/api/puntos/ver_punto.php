<?php

Route::prefix('puntos/ver')->namespace('Puntos')->group(function() {
    $controller = "VerPuntoController";
    Route::get('datos/{idPunto}', "$controller@datos");
});