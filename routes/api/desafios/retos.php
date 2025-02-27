<?php

use App\Http\Controllers\Desafios\RetosController;

Route::group(['prefix' => 'retos'], function () {
    Route::get('{idReto}', [RetosController::class, 'ver']);
    Route::post('crear', [RetosController::class, 'crear']);
    Route::put('editar/{idReto}', [RetosController::class, 'editar']);
});