<?php
Route::prefix('home')->group(function ()
{
    Route::get('get-tribus', 'Home\TribusController@getTribus');
});