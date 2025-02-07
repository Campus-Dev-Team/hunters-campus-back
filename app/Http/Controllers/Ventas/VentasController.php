<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VentasController extends Controller
{
    public function getVentas()
    {
        try {
            
            $bucket = config("filesystems.disks.s3.bucket");
            $tribus = DB::table('users')
            ->select()
            ->selectRaw("(select COUNT(1) from users_miembros where id_user = users.id AND estado = 1) as miembros_activos")
            ->selectRaw("(select COUNT(1) from users_miembros where id_user = users.id AND estado = 1 AND leeche_cliente_id is NOT NULL) as miembros_verificados")
            ->selectRaw("(select IFNULL(CONCAT('https://$bucket.s3.amazonaws.com/',users.logo),'/img/no-imagenes/tribu_no_imagen.svg')) as logo")
            ->where('tipo',1)
            ->orderBy('nombre','ASC')
            ->get();

            $meses = collect();
            for ($i=1; $i < 11 ; $i++) { 
                
                $mes = CarbonImmutable::create(2022,$i,1);
                $finMes = $mes->endOfMonth();

                $meses->push([ 'mes' => $i, 'inicio' => $mes, 'fin' => $finMes ]);

            }

            $nombresMeses = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Sept.','Agosto','Octubre'];
            $colores =['','#01B8AA', '#374649', '#FD625E', '#F2C80F', '#717B7D', '#95DBF1', '#BA6BFF', '#3275E8', '#E4573E', '#9FD891'];
            foreach ($tribus as $key => $tribu) {
                $miembros = DB::table('users_miembros')
                ->select('lider','id_user','leeche_cliente_id','comercial','inicio','fin','estado')
                ->selectRaw("(SELECT GROUP_CONCAT(id) FROM leeche_2021.users where id_user_referido = users_miembros.leeche_cliente_id) as referidos")
                ->where('id_user',$tribu->id)
                ->get();

                $graficaMeses = collect();

                foreach ($miembros as $key2 => $miembro) {
                    $referidos = explode(",",$miembro->referidos . "," . $miembro->leeche_cliente_id);
    
                    $compras = collect();
                    foreach ($meses as $key2 => $mes) {
                       $comprasMes = DB::table('leeche_2021.pedidos')
                                    ->selectRaw("SUM(valor_productos - valor_descuento + valor_impuestos) as total")
                                    ->whereIn('estado',[4,31,32,33,34])
                                    ->whereIn('created_by',$referidos)
                                    ->whereBetween('entrega_fecha',[$mes['inicio'],$mes['fin']])
                                    ->whereBetween('entrega_fecha',[$miembro->inicio,$miembro->fin])
                                    ->get()
                                    ->sum('total') / 1000;
    
                        if($miembro->comercial && $comprasMes > 4000){
                            $comprasMes = 4000;
                        }
    
                        $compras->push([
                            'mes' => $mes['mes'],
                            'total' => $comprasMes
                        ]);
    
                        $graficaMeses->push(['mes'=> $mes['mes'],'total' => $comprasMes]);
    
                    }
    
                }
    
                $totalAcumulado = $graficaMeses->sum('total');

                $graficaMeses = $graficaMeses->groupBy('mes')->map(function ($item,$keyMes) use ($nombresMeses, $colores){
                    return collect([
                        'mes' => $keyMes,
                        'nombre' => $nombresMeses[$keyMes],
                        'color' => $colores[$keyMes],
                        'total' => round($item->sum('total'))
                    ]);
                })->values();

                $tribu->meses =  $graficaMeses;
                $tribu->total_ventas = $totalAcumulado;


            }

            $maxPuntos = $tribus->max('total_ventas');

            foreach ($tribus as $key => $tribu) {
                
                $tribu->meses->map(function ($item,$keyMes) use ($maxPuntos){
                    
                    $item['porcentaje'] = round(($item['total'] / $maxPuntos) * 100, 1);
                    return $item;

                })->values();

            }

            return ['tribus' => $tribus->sortByDesc('total_ventas')->values()];


        } catch (\Throwable $th) {
            return $this->capturar($th);
        }
    }
}
