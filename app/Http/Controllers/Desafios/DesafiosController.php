<?php
namespace App\Http\Controllers\Desafios;

use DateTime;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Torneos;
use App\Models\Desafios;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class DesafiosController extends Controller
{
    private $meses = array('Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre');

    public function getDesafios(Request $request)
    {
        $bucket = config("filesystems.disks.s3.bucket");
        // Consultamos retos
        $retos = DB::table('retos')
        ->select(
            'retos.*',
            'users.color as tribu_color',
            'users.nombre as retador',
            'users.logo as retador_logo',
            'u2.nombre as oponente1',
            'u3.nombre as oponente2',
            'u4.nombre as oponente3',)
        ->selectRaw("(select IFNULL(CONCAT('https://$bucket.s3.amazonaws.com/',users.logo),'/img/no-imagenes/tribu_no_imagen.svg')) as retador_logo")
        ->selectRaw("(select IFNULL(CONCAT('https://$bucket.s3.amazonaws.com/',u2.logo),'/img/no-imagenes/tribu_no_imagen.svg')) as oponente1_logo")
        ->selectRaw("(select IFNULL(CONCAT('https://$bucket.s3.amazonaws.com/',u3.logo),'/img/no-imagenes/tribu_no_imagen.svg')) as oponente2_logo")
        ->selectRaw("(select IFNULL(CONCAT('https://$bucket.s3.amazonaws.com/',u4.logo),'/img/no-imagenes/tribu_no_imagen.svg')) as oponente3_logo")
        ->join('users', 'retos.created_by', 'users.id')
        ->leftJoin('users AS u2', 'retos.id_user_2', 'u2.id')
        ->leftJoin('users AS u3', 'retos.id_user_3', 'u3.id')
        ->leftJoin('users AS u4', 'retos.id_user_4', 'u4.id')
        ->where('estado', $request->estado)
        ->orderBy('fecha','ASC')
        ->orderBy('id','ASC')
        ->get()
        ->each( function($item) {
            $fechas = $this->controlFechas($item->fecha);
            $fecha_inicial = "Semana del ". $fechas['fecha_inicial']->format('d')." de ". $this->meses[($fechas['fecha_inicial']->format('n')) - 1];
            $fecha_final = "al ". $fechas['fecha_final']->format('d')." de ". $this->meses[($fechas['fecha_final']->format('n')) - 1];

            $item->fecha_agrupacion = $fecha_inicial." ". $fecha_final;

            $item->fecha_hora = $item->fecha . " " . $item->hora;

            $fecha_format_carbon = Carbon::parse($item->fecha);
            $item->fecha_format = $fecha_format_carbon->toFormattedDateString();
        });
        if (intval($request->estado) === 3) {
            $orden = $retos->sortByDesc('updated_at');
            return ['retos' => $orden->values()->all()];
        }
        // Agrupamos los retos por orden semana
        $retos_agrupados = $retos->groupBy('fecha_agrupacion')->map(function ($item, $key) {
            return [
                'semana' => $key,
                'semana_nombre' => $key,
                'retos' => $item,
            ];
        })->values();
        // Retornamos la información
        return ['retos' => $retos_agrupados];
    }

    public function controlFechas($fecha) {
        
        $fecha_inicial = Carbon::parse($fecha)->startOfWeek();
        $fecha_final = Carbon::parse($fecha)->endOfWeek(Carbon::SATURDAY);
        return [
            'fecha_inicial' => $fecha_inicial,
            'fecha_final' => $fecha_final,
        ];
    }

    public function getDesafiosEspeciales(Request $request)
    {
        // Consultamos torneos especiales
        $retos = DB::table('torneos')
        ->select(
            'torneos.id',
            'torneos.titulo as torneo',
            'torneos.fecha as torneo_fecha')
        ->selectRaw("(SELECT SUM(puntos_afectado) FROM users_puntos as us WHERE us.id_torneo = torneos.id) as puntos_torneo")
        ->selectRaw("(SELECT COUNT(1) FROM users_puntos as us WHERE us.id_torneo = torneos.id) as oportunidades")
        ->where('estado',$request->estado)
        ->orderBy('fecha','DESC')
        ->get();
        return ['retos' => $retos];
    }

    public function getOtrosDesafios(Request $request)
    {
        $bucket = config("filesystems.disks.s3.bucket");

        $retos = DB::table('users_puntos as up')
        ->select(
            'up.id',
            'up.manual_nombre',
            'up.created_at',
            'up.puntos_afectado',
            'up.afectacion',
            'users.nombre',
            'users.color'
        )
        ->selectRaw("(select IFNULL(CONCAT('https://$bucket.s3.amazonaws.com/',users.logo),'/img/no-imagenes/tribu_no_imagen.svg')) as logo")
        ->leftJoin('users','up.id_user','users.id')
        ->where('up.tipo',$request->tipo)
        ->orderBy('up.id','DESC')
        ->limit(200)
        ->get();

        foreach($retos as $item) {
            $fecha_formateada = strtotime($item->created_at);
            $dia = date('d', $fecha_formateada);
            $mes = date('m', $fecha_formateada);
            $mes_nombre = $this->meses[date('n', $mes) - 1];
            $anio = date('Y', $fecha_formateada);
            $mes_formateado = substr($mes_nombre, 0, 3);
            $item->fecha_nombre = "$dia $mes_formateado.de $anio";
        }
        // Retornamos la información
        return ['retos' => $retos];
    }
    
    public function getConsultarTorneo($id)
    {
        try {
            $torneo = Torneos::where('id',$id)->first();
            return ['torneo' => $torneo];
        } catch (\Exception $e) {
            return $this->capturar($e, 'Error', 'Error al consultar torneo');
        }
    }

    public function postCrearTorneo(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                // Validamos los campos requeridos
                $request->validate([
                    'titulo' => 'required',
                    'descripcion' => 'required',
                    'fecha' => 'required',
                    'created_by' => 'required',
                ]);
                $date = Carbon::parse($request->fecha);
                $date = $date->format('Y-m-d');
                $fecha_hoy = date('Y-m-d');
                if ($date < $fecha_hoy) {
                    return ["mensaje" => "La fecha no puede ser menor a la actual", "tipo" => "warning", "id_torneo" => ""];
                }
                // Guardamos la información en la tabla retos
                $torneo = Torneos::create([
                    'estado' => 1,
                    'titulo' => $request->titulo,
                    'descripcion' => $request->descripcion,
                    'fecha' => $date,
                    'created_by' => $request->created_by,
                ]);
                return ["mensaje" => "El torneo se ha creado correctamente", "tipo" => "success", "id_torneo" => $torneo->id];
            }, 5);
        } catch (\Exception $e) {
            return $this->capturar($e, 'Error', 'Error al crear torneo');
        }
    }
    
    public function putEditarTorneo($id, Request $request)
    {
        try {
            return DB::transaction(function () use ($request, $id) {
                // Validamos los campos requeridos
                $request->validate([
                    'titulo' => 'required',
                    'descripcion' => 'required',
                    'fecha' => 'required',
                ]);
                $info_torneo = Torneos::where('id', $id)->select('fecha','estado')->first();
                $date = Carbon::parse($request->fecha);
                $date = $date->format('Y-m-d');
               
                // Validamos si la fecha cambio o es la misma
                if ($date != $info_torneo->fecha) {
                    $fecha_hoy = date('Y-m-d');
                    if ($date < $fecha_hoy) {
                        return ["mensaje" => "La fecha no puede ser menor a la actual", "tipo" => "warning", "id_torneo" => ""];
                    }
                }
                // Validamos que el estado sea 1
                if ($info_torneo->estado != 1) {
                    return ["mensaje" => "No es posible editar este torneo", "tipo" => "warning", "id_torneo" => ""];
                }
                // Actualizamos la información en la tabla retos
                Torneos::where('id',$id)->update([
                    'titulo' => $request->titulo,
                    'descripcion' => $request->descripcion,
                    'fecha' => $date,
                ]);
                return ["mensaje" => "El torneo se ha actualizado correctamente", "tipo" => "success", "id_torneo" => $request->id];
            }, 5);
        } catch (\Exception $e) {
            return $this->capturar($e, 'Error', 'Error al editar un torneo');
        }
    }

    public function mostrar($id){
        $desafio = Desafios::with(['comentarios'])->find($id);
  
       switch ($desafio->cantidad) {
         case 2:
          $desafio->tribu1 = User::find($desafio->created_by);
          $desafio->tribu2 = User::find($desafio->id_user_2);
           break;
         case 3:
          $desafio->tribu1 = User::find($desafio->created_by);
          $desafio->tribu2 = User::find($desafio->id_user_2);
          $desafio->tribu3 = User::find($desafio->id_user_3);
           break;
         case 4:
          $desafio->tribu1 = User::find($desafio->created_by);
          $desafio->tribu2 = User::find($desafio->id_user_2);
          $desafio->tribu3 = User::find($desafio->id_user_3);
          $desafio->tribu4 = User::find($desafio->id_user_4);
           break;  
       }
  
        return $this->respuesta(true, $desafio, 'Desafío encontrado', 200);
      }
}
