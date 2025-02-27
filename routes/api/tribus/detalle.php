<?php

use App\Http\Controllers\Tribus\DetalleController;

Route::group(['prefix' => 'tribus/detalle'], function() {
    Route::get('datos/{idTribu}', [DetalleController::class, 'datos']);
});