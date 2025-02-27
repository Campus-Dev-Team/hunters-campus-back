<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Laravel 9 utiliza los namespaces completos para controladores
// Cambia estas rutas según sea necesario:

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// Asegúrate de que las rutas que requieren autenticación JWT 
// tengan el middleware correcto definido en Kernel.php
Route::group(['middleware' => ['jwt.verify']], function () {
    // Rutas protegidas aquí
});

// Nota: En Laravel 9, debes usar namespaces completos para los controladores:
// Ejemplo de cambio:
// Route::post('/login', 'Auth\LoginController@login');
// Debería cambiarse a:
// Route::post('/login', [\App\Http\Controllers\Auth\LoginController::class, 'login']);