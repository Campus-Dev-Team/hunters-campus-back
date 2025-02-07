<?php

Route::prefix('tribus/detalle')->namespace('Tribus')->group(function() {
    $controller = "DetalleController";
    Route::get('datos/{idTribu}', "$controller@datos");
});