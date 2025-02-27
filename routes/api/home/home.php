<?php

use App\Http\Controllers\Home\TribusController;

Route::group(['prefix' => 'home'], function () {
    Route::get('get-tribus', [TribusController::class, 'getTribus']);
});