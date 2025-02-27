<?php

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;

trait ApiResponserTrait
{
    /**
     * susccesResponse
     *
     * @param  array $data
     * @param  int  $code
     * @return \Illuminate\Http\JsonResponse
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
     * @param  array $data
     * @param  int $code
     * @return \Illuminate\Http\JsonResponse
     */
    public function errorResponse($data = [], $code = Response::HTTP_BAD_REQUEST): JsonResponse
    {
        $exito = collect(['exito' => false]);
        $data = $exito->merge($data);
        return response()->json($data, $code);
    }

    /**
     * Captura una excepción y genera un response a partir de ella
     *
     * @param  \Throwable $excepcion Excepción capturada
     * @param  string|null $titulo Título del error desplegado para el cliente
     * @param  string|null $mensaje Mensaje del error desplegado para el cliente
     * @param  int $http_status_code código de error HTTP, con el que responderá la petición (4xx)
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function capturar($excepcion, ?string $titulo = null, ?string $mensaje = null, int $http_status_code = Response::HTTP_BAD_REQUEST): Response
    {
        $validationException = is_a($excepcion, 'Illuminate\Validation\ValidationException');

        if ($validationException) {
            $titulo = $titulo ?? 'Ha ocurrido un error al validar los datos';
            $mensaje = collect($excepcion->errors())->flatten()->join(' ');
        }

        $http_status_code = $validationException || $http_status_code === 0 ? Response::HTTP_UNPROCESSABLE_ENTITY : $http_status_code;

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
