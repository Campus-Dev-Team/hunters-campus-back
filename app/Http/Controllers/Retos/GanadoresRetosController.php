<?php

namespace App\Http\Controllers\Retos;

use App\Http\Controllers\Controller;
use App\Models\Retos;
use App\Models\UsersPuntos;
use Illuminate\Http\Request;
use DB;

class GanadoresRetosController extends Controller
{
    public function getParticipantesReto($idReto)
    {
        try {

            $reto = DB::table('retos')->find($idReto);

            if (!$reto || $reto->estado != 2) {
                return response()->json(['mensaje' => "El reto no existe ó no puede ser finalizado"], 422);
            }

            $idsTribus = [$reto->created_by, $reto->id_user_2, $reto->id_user_3, $reto->id_user_4];
            $idsTribus = collect($idsTribus)->whereNotNull()->values();

            $bucket = config("filesystems.disks.s3.bucket");

            $tribus = DB::table('users')
                ->select('id', 'nombre')
                ->selectRaw("(select IFNULL(CONCAT('https://$bucket.s3.amazonaws.com/',users.logo),'/img/no-imagenes/tribu_no_imagen.svg')) as logo")
                ->whereIn('id', $idsTribus)
                ->get();

            $miembros = DB::table('users_miembros')
                ->select('id as id_miembro', 'lider', 'id_user')
                ->selectRaw("CONCAT_WS(' - ',nombre,empresa) as nombre_completo")
                ->selectRaw("0 as checked")
                ->whereIn('id_user', $idsTribus)
                ->where('estado',1)
                ->orderBy('nombre')
                ->get();

            foreach ($tribus as $key => $value) {
                $miembrosTribu = $miembros->where('id_user', $value->id)->sortByDesc('lider')->values();
                $value->miembros = $miembrosTribu;
            }

            $tribusOrdenadas = collect();

            foreach ($idsTribus as $key => $value) {
                $t = $tribus->where('id', $value)->first();
                $tribusOrdenadas->push($t);
            }

            return [
                'tribus' => $tribusOrdenadas,
                'ids' => $idsTribus
            ];
        } catch (\Throwable $th) {
            return $this->capturar($th);
        }
    }
    public function setGanadorReto(Request $request, $idReto)
    {
        try {
            return DB::transaction(function () use ($request, $idReto) {

                $reto = Retos::find($idReto);

                if (!$reto || $reto->estado != 2) {
                    return response()->json(['mensaje' => "El defafio no existe ó no puede ser finalizado"], 422);
                }

                $reto->id_user_ganador = $request->ganador;
                $reto->estado = 3;
                $reto->updated_by = $request->id_user;
                $reto->save();

                $idsTribus = [$reto->created_by, $reto->id_user_2, $reto->id_user_3, $reto->id_user_4];
                $tribusParticipantes = collect($idsTribus)->whereNotNull()->values();

                foreach ($tribusParticipantes as $key => $value) {

                    $puntosAntiguos = DB::table('users_puntos')->where('id_user', $value)->orderBy('id', 'DESC')->first();
                    $puntosAntiguos =  $puntosAntiguos ?  $puntosAntiguos->puntos_nuevos : 0;

                    if ($value == $request->ganador) {

                        $puntosAfectados = $reto->puntos * ($reto->cantidad - 1);
                        $model = new UsersPuntos();
                        $model->tipo = 1;
                        $model->id_user = $value;
                        $model->afectacion = 1;
                        $model->puntos_afectado = $puntosAfectados;
                        $model->puntos_anteriores =  $puntosAntiguos;
                        $model->puntos_nuevos = $puntosAntiguos + $puntosAfectados;
                        $model->id_reto = $idReto;
                        $model->created_by = $request->id_user;
                        $model->save();
                        continue;
                    }

                    $puntosAfectados = $reto->puntos;
                    $puntosNuevos = $puntosAntiguos - $puntosAfectados;
                    $model = new UsersPuntos();
                    $model->tipo = 1;
                    $model->id_user = $value;
                    $model->afectacion = 2;
                    $model->puntos_afectado = $puntosAfectados;
                    $model->puntos_anteriores =  $puntosAntiguos;
                    $model->puntos_nuevos = $puntosNuevos < 0 ? 0 : $puntosNuevos;
                    $model->id_reto = $idReto;
                    $model->created_by = $request->id_user;
                    $model->save();
                }


                $tmp = [];
                foreach ($request->participantes as $key => $value) {
                    $tmp[] = [
                        'id_miembro' => $value,
                        'id_reto' => $idReto,
                        'puntos' => 1,
                        'created_by' => $request->id_user
                    ];
                }

                DB::table('retos_miembros')->insert($tmp);

                return ['exito' => true];
            }, 5);
        } catch (\Throwable $th) {
            return $this->capturar($th);
        }
    }
}
