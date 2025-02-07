<?php
Route::prefix('util')->namespace('Util')->group(function () {
    $controlador = 'UtilController';
    Route::get('tribus-select', "$controlador@tribusSelect");
    Route::get('integrantes-tribu-select/{id}', "$controlador@integrantesTribuSelect");
});