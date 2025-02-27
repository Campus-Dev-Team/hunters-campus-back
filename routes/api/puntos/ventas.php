<?php

use App\Http\Controllers\Ventas\VentasController;

Route::group([], function() {
    Route::get('ventas', [VentasController::class, 'getVentas']);
});