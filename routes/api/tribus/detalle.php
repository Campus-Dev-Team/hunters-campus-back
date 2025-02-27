<?php

use App\Http\Controllers\Home\TribusController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'tribus/detalle'], function() {
    Route::get('datos/{idTribu}', [TribusController::class, 'getTribus']);
});
