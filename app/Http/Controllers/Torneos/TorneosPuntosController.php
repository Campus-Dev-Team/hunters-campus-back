<?php

namespace App\Http\Controllers\Torneos;

use App\Http\Controllers\Controller;
use App\Models\UsersPuntos;
use Illuminate\Http\Request;
use DB;

class TorneosPuntosController extends Controller
{
    public function getTribus()
    {
        try {

            $bucket = config("filesystems.disks.s3.bucket");

            $tribus = DB::table('users')
                ->select('id', 'nombre')
                ->selectRaw("(select IFNULL(CONCAT('https://$bucket.s3.amazonaws.com/',users.logo),'/img/no-imagenes/tribu_no_imagen.svg')) as logo")
                ->where('tipo', 1)
                ->orderBy('nombre')
                ->get();

            $miembros = DB::table('users_miembros')
                ->select('id as id_miembro', 'lider', 'id_user')
                ->selectRaw("CONCAT_WS(' - ',nombre,empresa) as nombre_completo")
                ->selectRaw("0 as checked")
                ->where('estado',1)
                ->orderBy('nombre')
                ->get();

            foreach ($tribus as $key => $value) {
                $miembrosTribu = $miembros->where('id_user', $value->id)->sortByDesc('lider')->values();
                $value->miembros = $miembrosTribu;
            }

            return ['tribus' => $tribus];
        } catch (\Throwable $th) {
            return $this->capturar($th);
        }
    }
    public function getTorneo($idTorneo)
    {
        try {
            $torneo = DB::table('torneos')->find($idTorneo);

            if (!$torneo || $torneo->estado == 0) {
                return response()->json(['mensaje' => "El torneo no existe ó fue eliminado"], 422);
            }
            $bucket = config("filesystems.disks.s3.bucket");
            $tribus = DB::table('users')
                ->select('id', 'nombre')
                ->selectRaw("(select IFNULL(CONCAT('https://$bucket.s3.amazonaws.com/',users.logo),'/img/no-imagenes/tribu_no_imagen.svg')) as logo")
                ->selectRaw("'' as puntos")
                ->selectRaw("'' as comentario")
                ->where('tipo', 1)
                ->orderBy('nombre')
                ->get();

            return ['tribus' => $tribus];
        } catch (\Throwable $th) {
            return $this->capturar($th);
        }
    }
    public function asignarPuntosTorneo(Request $request, $idTorneo)
    {
        try {
            return DB::transaction(function () use ($request, $idTorneo) {
                $torneo = DB::table('torneos')->find($idTorneo);

                if (!$torneo || $torneo->estado == 0) {
                    return response()->json(['mensaje' => "El torneo no existe ó fue eliminado"], 422);
                }

               
                foreach ($request->tribus as $key => $value) {

                    $puntosAntiguos = DB::table('users_puntos')->where('id_user', $value['id'])->orderBy('id', 'DESC')->first();
                    $puntosAntiguos =  $puntosAntiguos ?  $puntosAntiguos->puntos_nuevos : 0;
                    $puntosAfectados = $value['puntos'];

                    $model = new UsersPuntos();
                    $model->tipo = 2;
                    $model->id_user = $value['id'];
                    $model->afectacion = 1;
                    $model->puntos_afectado = $puntosAfectados;
                    $model->puntos_anteriores =  $puntosAntiguos;
                    $model->puntos_nuevos = $puntosAntiguos + $puntosAfectados;
                    $model->id_torneo = $idTorneo;
                    $model->manual_descripcion = $value['comentario'];
                    $model->created_by = $request->id_user;
                    $model->save();

                }

                return ['exito' => true];
            }, 5);
        } catch (\Throwable $th) {
            return $this->capturar($th);
        }
    }
    public function asignarPuntosEvento(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {

                $idTribu = $request->id_tribu;
                $puntosAntiguos = DB::table('users_puntos')->where('id_user', $idTribu)->orderBy('id', 'DESC')->first();
                $puntosAntiguos =  $puntosAntiguos ?  $puntosAntiguos->puntos_nuevos : 0;
                $puntosAfectados = $request->puntos;

                $puntosNuevos = $request->afectacion == 1 ? $puntosAntiguos + $puntosAfectados : $puntosAntiguos - $puntosAfectados;

                $model = new UsersPuntos();
                $model->tipo = 3;
                $model->id_user = $idTribu;
                $model->afectacion = $request->afectacion;
                $model->puntos_afectado = $puntosAfectados;
                $model->puntos_anteriores =  $puntosAntiguos;
                $model->puntos_nuevos = $puntosNuevos < 0 ? 0 : $puntosNuevos;
                $model->manual_nombre = $request->titulo;
                $model->manual_descripcion = $request->descripcion;
                $model->created_by = $request->id_user;
                $model->save();

                $tmp = [];
                foreach ($request->participantes as $key => $value) {
                    $tmp[] = [
                        'id_miembro' => $value,
                        'id_puntos' => $model->id,
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
