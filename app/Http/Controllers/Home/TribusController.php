<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;

class TribusController extends Controller
{
    public function getTribus()
    {
        try {
            $dia1SemanaActual = Carbon::now()->startOfWeek()->isoFormat('YYYY-MM-DD');
            $dia7SemanaActual = Carbon::now()->endOfWeek()->isoFormat('YYYY-MM-DD');
            
            $dia1SemanaAnterior = Carbon::now()->subWeek()->startOfWeek()->isoFormat('YYYY-MM-DD');
            $dia7SemanaAnterior = Carbon::now()->subWeek()->endOfWeek()->isoFormat('YYYY-MM-DD');
            
            $bucket = config("filesystems.disks.s3.bucket");

            $tribus = DB::table('users')
            ->select()
            ->selectRaw("(SELECT up.puntos_nuevos from users_puntos up where up.id_user = users.id ORDER BY up.id DESC LIMIT 1) as total_puntos")
            ->selectRaw("(select IFNULL(CONCAT('https://$bucket.s3.amazonaws.com/',users.logo),'/img/no-imagenes/tribu_no_imagen.svg')) as logo")
            ->selectRaw("(select COUNT(1) from retos where estado IN (1,2,3) and created_by = users.id OR id_user_2 = users.id OR id_user_3 = users.id OR id_user_4 = users.id) as cant_total_participaciones")
            ->selectRaw("(
                SELECT COUNT(1) 
                FROM retos as r 
                WHERE r.estado IN (2,3) AND Date(r.updated_at) >= '$dia1SemanaActual' AND Date(r.updated_at) <= '$dia7SemanaActual' 
                AND r.estado = 3
                AND (r.created_by = users.id OR r.id_user_2 = users.id OR r.id_user_3 = users.id OR r.id_user_4 = users.id)
                ) as cant_participaciones_actuales")
            ->selectRaw("(
                SELECT COUNT(1) 
                FROM retos as r 
                WHERE r.estado IN (2,3) AND Date(r.updated_at) >= '$dia1SemanaAnterior' AND Date(r.updated_at) <= '$dia7SemanaAnterior'
                AND r.estado = 3
                AND (r.created_by = users.id OR r.id_user_2 = users.id OR r.id_user_3 = users.id OR r.id_user_4 = users.id)
                ) as cant_participaciones_semana_anterior")
            ->where('tipo',1)
            ->orderBy('nombre','ASC')
            ->get();
            
            $miembros = DB::table('users_miembros')
            ->select('lider','id_user','comercial','inicio','fin','estado')
            ->selectRaw("CONCAT_WS(' - ',nombre,empresa) as nombre_completo")
            ->selectRaw("(SELECT IFNULL(SUM(puntos),0) FROM retos_miembros where id_miembro = users_miembros.id) cant_participaciones")
            ->whereIn('id_user', $tribus->pluck('id'))
            ->orderBy('cant_participaciones', 'DESC')
            ->get();

            foreach ($tribus as $key => $value) {
                $miembrosTribu = $miembros->where('id_user', $value->id)->sortByDesc('lider')->values();

                $puntos = DB::table('users_puntos')
                ->select('puntos_afectado as puntos', 'afectacion', 'id_reto', 'id_torneo', 'tipo')
                ->where('id_user', $value->id)
                ->get();

                $puntosRetos = $puntos->whereNull('id_torneo')->values();
                $value->puntos_retos = $puntosRetos->where('afectacion', 1)->sum('puntos') - $puntosRetos->where('afectacion', 2)->sum('puntos');
                
                $puntosTorneos = $puntos->whereNotNull('id_torneo')->values();
                $value->puntos_torneos = $puntosTorneos->where('afectacion', 1)->sum('puntos') - $puntosTorneos->where('afectacion', 2)->sum('puntos');

                $value->miembros = $miembrosTribu->where('estado', 1)->values()->all();
            }

            $tribus = $tribus->sortByDesc('total_puntos')->values()->all();

            return ['tribus' => $tribus];

        } catch (\Throwable $th) {
            return $this->capturar($th);
        }
    }
}
