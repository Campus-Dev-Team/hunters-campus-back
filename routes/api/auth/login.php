<?php
Route::prefix('auth')->namespace('Auth')->group(function ()
{
    Route::post('login', 'LoginController@authenticate');
    Route::post('pedir-password', 'LoginController@pedirPassword');

});