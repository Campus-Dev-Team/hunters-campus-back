<?php

namespace App\Http\Controllers;

use App\Http\Traits\ApiResponserTrait;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Symfony\Component\HttpFoundation\Response;

class Controller extends BaseController {
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, ApiResponserTrait;

  /**
   * Para responder en controladores
   *
   * @param boolean $success
   * @param Array|Object $data
   * @param String $mensaje
   * @param http_code $status
   * @return json
   */
public function respuesta(bool $success, $data, $status, String $mensaje)  {
    return response()->json([
      'success' => $success,
      'data' => $data,
      'mensaje' => $mensaje
    ], $status);
  }
}
