<?php

namespace App\Helpers;

use Storage, Image;
class UtilHelper
{
  /**
   * @author Alexis V.
   * Verifica si un string dado es un email
   *
   * @param String $str
   * @return bool
   */
  public static function es_email($str) {

      return (!preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $str)) ? FALSE : TRUE;

  }

  /**
   * @author Alexis V.
   * Función para retornar una imágen por defecto
   *   
   *   tipo 1 = sin usuario, tipo 2 = sin producto, tipo 3 = sin producto v2, tipo 4 = tienda, 6= sin promocion, 7= sin cedis
   *
   * @param int $tipo
   * @return String
   */
      
  public function no_imagen(int $tipo = null)
  {
      switch ($tipo) {
          case 1:
              return "/img/no-imagen/sin_user.png";
              break;
          case 2:
              return "/img/sin_datos/mercado.svg";
              break;
          case 3:
              return "/img/sin_datos/mercado-1.svg";
              break;
          case 4:
              return "/img/no-imagen/sin_cliente.svg"; // sin foto de la tienda
              break;
          case 5:
              return "/img/no-imagen/pedidos_manuales.png"; // avatar para usuario sin foto o para pedidos manuales
              break;
          case 6:
              return "/img/no-imagen/promociones.png";
              break;
          case 7:
              return "/img/no-imagen/cedis.svg";
              break;
          case 8:
              return "/img/modales/Grupo 25651.svg";
              break;
          case 9:
              return "/img/no-imagen/no-imagen-licencia.svg"; // Sin licencia
              break;
          case 10:
              return "/img/no-imagen/tribu_no_imagen"; // Tribu sin foto
              break;
          default:
              return "/img/no-imagen/default.jpg";
              break;
      }
  }

  /**
   * @author Alexis V.
   * Para responder en controladores
   *
   * @param boolean $success
   * @param Array|Object $data
   * @param String $mensaje
   * @param http_code $status
   * @return json
   */
  public static function respuesta(bool $success,$data,String $mensaje = '',$status){
      return response()->json([
          'success' => $success,
          'data' => $data,
          'mensaje' => $mensaje
      ],$status);
  }

  /** 
  *  @author Alexis V.
  *  funcion para guardar la imagen con el porcentaje y la ruta deseada
  *  $img @binario Archivo de imágen
  *  $dimension @int porcentaje en que desea la imagen
  *  $ruta @string dirección donde estará alojada
  *  $mini @boolean guardar una versión mini
  */
  public function guardar_imagen_porcentaje($img, $dimension, $ruta, $mini = false, $dimension_mini = 100)
  {
      try {
          // return $img;
          $imagen_file = Image::make($img);
          $imagen_file_ancho = $imagen_file->width();
          $imagen_file_alto = $imagen_file->height();

          //se obtiene el lado maximo de la imagen
          if ($imagen_file_ancho > $imagen_file_alto) {
              $imagen_max = $imagen_file_ancho;
          } else {
              $imagen_max = $imagen_file_alto;
          }

          //Se obtiene el porcentaje para cambiar el tamaño de la imagen para que no se distorcione
          $imagen_porcentaje = ($dimension / $imagen_max);

          //Solo se cambia de tamaño cuando alguno de los lados pasa la dimension requerida
          if ($imagen_porcentaje < 1) {
              $imagen_alto = $imagen_file_alto * $imagen_porcentaje;
              $imagen_ancho = $imagen_file_ancho * $imagen_porcentaje;
              $imagen = $imagen_file->resize($imagen_ancho, $imagen_alto);
          } else {
              $imagen = Image::make($img);
          }

          $ex = explode('/', $imagen->mime);
          $ext = end($ex);
          $nombre_aleatorio = uniqid(rand(), true) . str_replace(" ", "", microtime()) . ".$ext";
          $subruta = ($ruta != '' ? "$ruta/" : '') . $nombre_aleatorio;
          $subruta_mini = '';
          Storage::put($subruta, (string)$imagen->encode($ext));
          if ($mini) {
              $imagen_mini = $this->formatea_imagen($imagen, $dimension_mini);
              $nombre_aleatorio_mini = uniqid(rand(), true) . str_replace(" ", "", microtime()) . ".$ext";
              $subruta_mini = ($ruta != '' ? "$ruta/" : '') . $nombre_aleatorio_mini;
              Storage::put($subruta_mini, (string)$imagen_mini->encode($ext));
          }
          return [
              'estado' => true,
              'ruta' => $subruta,
              'ruta_mini' => $subruta_mini
          ];
      } catch (\Exception $e) {
          //return $e;

          dd($e);
          return [
              'estado' => false,
              'ruta' => null,
              'ruta_mini' => null
          ];
      }
  }

  /**
   * @author Alexis V.
   * Formatea una imágen
   *
   * @param bin $imagen
   * @param int $porcentaje
   * @return void
   */
  public function formatea_imagen($imagen, $porcentaje)
  {
      $imagen_file = Image::make($imagen);
      $imagen_file_ancho = $imagen_file->width();
      $imagen_file_alto = $imagen_file->height();

      if ($imagen_file_ancho > $imagen_file_alto) {
          $imagen_max = $imagen_file_ancho;
      } else {
          $imagen_max = $imagen_file_alto;
      }
      $imagen_porcentaje = ($porcentaje / $imagen_max);
      if ($imagen_porcentaje < 1) {
          $imagen_alto = $imagen_file_alto * $imagen_porcentaje;
          $imagen_ancho = $imagen_file_ancho * $imagen_porcentaje;
          $imagen = $imagen_file->resize($imagen_ancho, $imagen_alto);
      } else {
          $imagen = Image::make($imagen);
      }
      return $imagen;
  }

  // Regresa la url absoluta de un archivo en el storage
  public function temporalUrl($path, $time = 60)
  {
      if (!$path) {
          return null;
      }

      return Storage::Url($path);
  }

  // Elimina un archivo del storage
  public function eliminarArchivo($path){
      return Storage::delete($path);
  }

}
