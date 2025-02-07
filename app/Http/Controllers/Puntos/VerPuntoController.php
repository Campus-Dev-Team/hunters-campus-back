<?php

namespace App\Http\Controllers\Puntos;

use App\Http\Controllers\Controller;
use App\Http\Traits\ImagesTrait;
use App\Models\User;
use App\Models\UsersMiembros;
use App\Models\UsersPuntos;
use Illuminate\Http\Request;

class VerPuntoController extends Controller {
    use ImagesTrait;


    public function datos($idPunto) {
        try {
            $punto = UsersPuntos::select(
                'id',
                'manual_nombre',
                'manual_descripcion',
                'id_user',
                'tipo',
                'puntos_afectado',
                'puntos_anteriores',
                'puntos_nuevos',
                'created_by',
                'created_at',
            )
                ->find($idPunto);
            if (!$punto || $punto->tipo != 3) {
                return $this->errorResponse(['cod' => 'nep']);
            }

            $created = User::select(
                'id',
                'nombre',
            )
                ->find($punto->created_by);


            $lider = UsersMiembros::select(
                'users_miembros.id',
                'users_miembros.nombre',
                'u.logo',
                'u.id as tribu'
            )
            ->join('users as u', 'u.id', 'users_miembros.id_user')
            ->where('users_miembros.lider', 1)
            ->where('users_miembros.id_user', $punto->id_user)
            ->orderBy('users_miembros.id')->first();

            $lider->imagen = $lider->logo ? $this->temporalUrl($lider->logo) : $this->noImagen(1);

            $response = [
                'punto' => $punto,
                'created' => $created,
                'lider' => $lider,
            ];

            return $this->successResponse($response);
        } catch (\Throwable $th) {
            return $this->capturar($th);
        }
    }
}
