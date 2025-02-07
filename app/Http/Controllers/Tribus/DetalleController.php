<?php

namespace App\Http\Controllers\Tribus;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Home\TribusController;
use App\Models\Retos;
use App\Models\Torneos;
use App\Models\User;
use App\Models\UsersPuntos;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Faker\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DetalleController extends Controller {

    public function faker() {
        $datos = [];

        $usuarios = User::pluck('id');

        for ($i = 0; $i < 10; $i++) {
            $faker = Factory::create();
            $dato = [
                'id_user' => $faker->randomElement($usuarios),
                'tipo' => $faker->randomElement([1, 2, 3]),
                'puntos_afectado' => $faker->randomDigit(),
                'puntos_anteriores' => $faker->randomDigit(),
                'puntos_nuevos' => $faker->randomDigit(),
                'afectacion' => $faker->randomElement([1, 2]),
                'created_by' => $faker->randomElement($usuarios),
                'created_at' => $faker->dateTimeBetween('-1 month', 'now'),
            ];
            $datos[] = $dato;
        }

        UsersPuntos::insert($datos);

        return $datos;
    }

    public function datos($idTribu) {
        try {
            $bucket = config("filesystems.disks.s3.bucket");
            $user = User::select('id', 'nombre', 'logo', 'tipo', 'color')
            ->selectRaw("(select IFNULL(CONCAT('https://$bucket.s3.amazonaws.com/',users.logo),'/img/no-imagenes/tribu_no_imagen.svg')) as imagen")
            ->find($idTribu);
            if(!$user || $user->tipo != 1){
                return $this->errorResponse(['cod' => 'tne']);
            }

            $puntosTribu = UsersPuntos::where('id_user', $idTribu)->latest('id')->first();

            $enviados = Retos::where('created_by', $idTribu)->whereIn('estado', [1, 2, 3])->count();

            $aceptados = Retos::where(function ($query) use ($idTribu) {
                return $query
                    ->where('id_user_2', $idTribu)
                    ->orWhere('id_user_3', $idTribu)
                    ->orWhere('id_user_4', $idTribu);
            })->whereIn('estado', [1, 2, 3])->count();

            $ganados = Retos::select('puntos')->where('id_user_ganador', $idTribu)->where('estado', 3)->get();

            $perdidos = Retos::select('puntos')->where('id_user_ganador', '!=', $idTribu)
                ->where('estado', 3)
                ->where(function ($query) use ($idTribu) {
                    return $query
                        ->where('id_user_2', $idTribu)
                        ->orWhere('created_by', $idTribu)
                        ->orWhere('id_user_3', $idTribu)
                        ->orWhere('id_user_4', $idTribu);
                })
                ->get();
                

            $puntos = UsersPuntos::where('id_user', $idTribu)
                ->orderByDesc('id')
                ->take(200)
                ->get()
                ->map(function ($item) {
                    $nombre = 'Sin titulo';
                    if ($item->tipo == 1) {
                        $reto = Retos::find($item->id_reto);
                        $nombre = $reto ? $reto->titulo : 'Sin titulo';
                        $fecha = $reto->updated_at;
                    } else if ($item->tipo == 2) {
                        $reto = Torneos::find($item->id_torneo);
                        $nombre = $reto ? $reto->titulo : 'Sin titulo';
                        $fecha = $item->created_at;
                    } else if ($item->tipo == 3) {
                        $nombre = $item->manual_nombre ?? 'Sin titulo';
                        $fecha = $item->created_at;
                    }
                    return [
                        'id' => $item->id,
                        'tipo' => $item->tipo,
                        'id_reto' => $item->id_reto,
                        'id_torneo' => $item->id_torneo,
                        'puntos_anteriores' => $item->puntos_anteriores,
                        'puntos_nuevos' => $item->puntos_nuevos,
                        'puntos_afectados' => $item->puntos_afectado,
                        'nombre' => $nombre,
                        'fecha' => $fecha,
                    ];
                });

            $miembros = DB::table('users_miembros')
                ->select('lider','id_user','leeche_cliente_id','comercial','inicio','fin','estado')
                ->selectRaw("CONCAT_WS(' - ',nombre,empresa) as nombre_completo")
                ->selectRaw("(SELECT IFNULL(SUM(puntos),0) FROM retos_miembros where id_miembro = users_miembros.id) cant_participaciones")
                ->selectRaw("(SELECT GROUP_CONCAT(id) FROM leeche_2021.users where id_user_referido = users_miembros.leeche_cliente_id) as referidos")
                ->where('id_user',$idTribu)
                ->orderBy('lider','DESC')
                ->orderBy('cant_participaciones','DESC')
                ->get();

            $user->promedio_puntos = $miembros->max('cant_participaciones') * 0.4;
            
            $meses = collect();
            for ($i=1; $i < 11 ; $i++) { 
                
                $mes = CarbonImmutable::create(2022,$i,1);
                $finMes = $mes->endOfMonth();

                $meses->push([
                    'mes' => $i,
                    'inicio' => $mes,
                    'fin' => $finMes
                    ]);

            }

            $graficaMeses = collect();

            foreach ($miembros as $key => $value) {
                $referidos = explode(",",$value->referidos . "," . $value->leeche_cliente_id);

                $compras = collect();
                foreach ($meses as $key2 => $mes) {
                   $comprasMes = DB::table('leeche_2021.pedidos')
                                ->selectRaw("SUM(valor_productos - valor_descuento + valor_impuestos) as total")
                                ->whereIn('estado',[4,31,32,33,34])
                                ->whereIn('created_by',$referidos)
                                ->whereBetween('entrega_fecha',[$mes['inicio'],$mes['fin']])
                                ->whereBetween('entrega_fecha',[$value->inicio,$value->fin])
                                ->get()
                                ->sum('total') / 1000;

                    if($value->comercial && $comprasMes > 4000){
                        $comprasMes = 4000;
                    }

                    $compras->push([
                        'mes' => $mes['mes'],
                        'total' => $comprasMes
                    ]);

                    $graficaMeses->push(['mes'=> $mes['mes'],'total' => $comprasMes]);
                }

                $value->compras = $compras->sum('total');

            }

            
            
            $totalAcumulado = $graficaMeses->sum('total') > 0 ? $graficaMeses->sum('total') : 1;
            $miembroMayor = $miembros->max('compras')  > 0 ? $miembros->max('compras') : 1;;
            
            foreach ($miembros as $key => $value) {
                $porcGeneral = ($value->compras / $totalAcumulado) * 100;
                $porcInterno = ($value->compras / $miembroMayor) * 100;

                $value->porcentaje_general = $porcGeneral;
                $value->porcentaje_interno = $porcInterno;
            }

            $miembrosParticipacion = $miembros->sortByDesc('compras')->values()->all();

            $nombresMeses = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Sept.','Agosto','Octubre'];
            $graficaMeses = $graficaMeses->groupBy('mes')->map(function ($item,$key) use ($nombresMeses,$graficaMeses){
                return collect([
                    'mes' => $key,
                    'nombre' => $nombresMeses[$key],
                    'total' => round($item->sum('total'))
                ]);
            });


            foreach ($graficaMeses as $key => $value) {
                if($key != 1){
                    $t = $graficaMeses[$key-1]['total'] + $value['total'];
                }else{
                    $t = $value['total'];
                }
                $value['total'] = $t;
            }

            $puntosTribu = $puntosTribu ? $puntosTribu->puntos_nuevos : 0;
            $user->puntos = $puntosTribu + $totalAcumulado;

            $controller = new TribusController();
            $listadoTribus = $controller->getTribus();
            $listadoTribus = $listadoTribus['tribus'];

            $posicion = collect($listadoTribus)->where('id',$idTribu)->keys()->first();

            $user->posicion = $posicion + 1;

            $response = [
                'enviados' => $enviados,
                'aceptados' => $aceptados,
                'ganados' => $ganados->count(),
                'puntos_ganados' => $ganados->sum('puntos'),
                'perdidos' => $perdidos->count(),
                'puntos_perdidos' => $perdidos->sum('puntos'),
                'user' => $user,
                'puntos' => $puntos,
                'miembros' => $miembros,
                'miembros_participacion' => $miembrosParticipacion,
                'grafica' => $graficaMeses->values()->all(),
                'total_acumulado' => $totalAcumulado,
                
            ];
            return $this->successResponse($response);
        } catch (\Throwable $th) {
            return $this->capturar($th);
        }
    }
}
