<?php
Route::prefix('retos')->namespace('Desafios')->group(function () {
    $controller = "RetosController";
    Route::get('{idReto}', "$controller@ver");
    Route::post('crear', "$controller@crear");
    Route::put('editar/{idReto}', "$controller@editar");
});