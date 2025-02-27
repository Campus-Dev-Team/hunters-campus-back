<?php

use App\Http\Controllers\Util\UtilController;

Route::prefix('util')->group(function () {
    Route::get('tribus-select', [UtilController::class, 'tribusSelect']);
    Route::get('integrantes-tribu-select/{id}', [UtilController::class, 'integrantesTribuSelect']);
});