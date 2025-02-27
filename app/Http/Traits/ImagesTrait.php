<?php

namespace App\Http\Traits;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

trait ImagesTrait {
    /*
        funcion para guardar la imagen con el porcentaje y la ruta deseada
        $img @binario Archivo de imágen
        $dimension @int porcentaje en que desea la imagen
        $ruta @string dirección donde estará alojada
        $mini @boolean guardar una versión mini
    */
    public function guardar_imagen_porcentaje($img, $dimension, $ruta, $mini = false, $dimension_mini = 100): array {
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
        } catch (\Throwable $e) {
            //return $e;
            return [
                'estado' => false,
                'ruta' => null,
                'ruta_mini' => null
            ];
        }
    }

    public function formatea_imagen($imagen, $porcentaje) {
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
    public function temporalUrl($path, $time = 60) {
        if (!$path) {
            return null;
        }

        return Storage::Url($path);
    }


    /**
     *
     * Funcion que retorna la ruta del no imagen en el front
     *
     * @param int $tipo 1 => Tribu, 2 => Admin
     * @return string ruta del front del no imagen
     **/
    public function noImagen(int $tipo = null): string {
        switch ($tipo) {
            case 1:
                return "/img/no-imagenes/tribu_no_imagen.svg";
                break;
            case 2:
                return "/img/no-imagenes/sin_asignar.svg";
                break;
            default:
                return "/img/no-imagenes/sin_asignar.svg";
                break;
        }
    }
}
