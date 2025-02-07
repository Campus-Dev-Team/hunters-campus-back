<?php

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

use Carbon\Carbon;


trait ApiResponserTrait
{
    /**
     * susccesResponse
     *
     * @param  string $data
     * @param  int  $code
     * @return Illuminate\Http\JsonResponse
     */
    public function successResponse($data = [], $code = Response::HTTP_OK): JsonResponse
    {
        $exito = collect(['exito' => true]);
        $data = $exito->merge($data);
        return response()->json($data, $code);
    }
    /**
     * errorResponse
     *
     * @param  string $message
     * @param  int $code
     * @return Illuminate\Http\JsonResponse
     */
    public function errorResponse($data = [], $code = 400): JsonResponse
    {
        $exito = collect(['exito' => false]);
        $data = $exito->merge($data);
        return response()->json($data, $code);
    }

    /**
     * Captura una excepción y genera un response a partir de ella
     *
     * @param  Exception $excepcion = Excepción capturada
     * @param  String $titulo (opcional) = Título del error desplegado para el cliente
     * @param  String $mensaje (opcional) = Mensaje del error desplegado para el cliente
     * @param  Int $http_status_code (opcional) = código de error HTTP, con el que responderá la petición (4xx)
     * @return Response
     */
    public function capturar($excepcion, String $titulo = null, String $mensaje = null, Int $http_status_code = 400): Response
    {
        $validationException = is_a($excepcion, 'Illuminate\Validation\ValidationException');

        if ($validationException) {
            $titulo = $titulo ?? 'Ha ocurrido un error al validar los datos';
            $mensaje = collect($excepcion->errors())->flatten()->join(' ');
        }

        $http_status_code = $validationException || $http_status_code === 0 ? 422 : $http_status_code;

        $response = [
            'timestamp'   => Carbon::now()->format('d/m/y h:i A'),
            'status'      => $http_status_code,
            'titulo'      => $titulo ?? 'Ha ocurrido un error al ejecutar la consulta',
            'mensaje'     => $mensaje ?? $excepcion->getMessage(),
            'error'       => "{$excepcion->getMessage()} - Archivo: {$excepcion->getFile()} - Línea: {$excepcion->getLine()}",
            'validaciones' => $validationException ? $excepcion->errors() : null
        ];

        return response($response, $http_status_code);
    }
}
