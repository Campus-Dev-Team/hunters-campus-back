<?php
Route::prefix('retos')->group(function ()
{
    Route::get('datos/{idReto}/participantes', 'Retos\GanadoresRetosController@getParticipantesReto');
    Route::put('{id_reto}/ganador/finalizar', 'Retos\GanadoresRetosController@setGanadorReto');
});