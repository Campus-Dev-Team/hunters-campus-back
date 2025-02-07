<?php

Route::namespace('Ventas')->group(function() {
    
    Route::get('ventas', "VentasController@getVentas");
});