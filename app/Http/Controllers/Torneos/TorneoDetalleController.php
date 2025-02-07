<?php

namespace App\Http\Controllers\Torneos;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\RetosMiembros;
use App\Models\Torneos;
use App\Models\TorneosComentarios;
use App\Models\TorneosPuntos;
use App\Models\UsersMiembros;
use App\Models\UsersPuntos;
use Illuminate\Support\Facades\Validator;

class TorneoDetalleController extends Controller
{
  public function mostrar($id)
  {
    try {
      $torneo = Torneos::with(['comentarios','tribu'])->find($id);  

      $torneo->puntos = UsersPuntos::select(
        'id_user',
        'puntos_afectado',
        'created_at',
        'manual_descripcion',
      )
      ->with(['tribu'])
      ->where('id_torneo',$torneo->id)
      ->where('tipo',2)
      ->orderBy('created_at','DESC')
      ->get();

      return $this->respuesta(true, $torneo, 'Desafío encontrado', 200);
    } catch (\Throwable $th) {
      return $this->capturar($th);
    }
  }

  public function crearComentario(Request $request){
    try {

        $validator = Validator::make($request->all(), [          
          'password' => 'string|required',
          'id_torneo' => 'required|exists:torneos,id',
          'comentario' => 'required|max:500',
        ]);

        if ($validator->fails()) {
          return $this->respuesta(false, $validator->errors(), 'Error en validación de campos', 400);
        }

        $tribu = User::where('password',$request->password)->first();

        if(!$tribu){
          return $this->respuesta(false, [], 'No se encontró la tribu', 200);
        }

        $comentario = new TorneosComentarios();

        $comentario->comentario = $request->comentario;
        $comentario->id_torneo = $request->id_torneo;
        $comentario->created_by = $tribu->id;

        $comentario->save();

        return $this->respuesta(true, $comentario, 'Comentario realizado con éxito', 201);
        


    } catch (\Throwable $th) {
      return $this->capturar($th);
    }
  }

  public function ranking($id){
    try {
      $torneo = Torneos::with(['comentarios','tribu'])->find($id); 
      $ranking = [];


      if($torneo){
        $ranking = User::select(
          'id',
          'nombre',
          'color',
          'logo'
        )
        ->where('tipo','!=',2)
        ->get();

        $ausencias = RetosMiembros::where('id_torneo',$torneo->id)->where('puntos',-1)->exists();
        
        foreach ($ranking as $tribu) {

          if($ausencias ){
            $tribu->torneo = collect([
              'puntos' => 0,
              'juegos' => 0,
              'victorias' => 0,
              'ausencias' => 0,
            ]);
          }else{
            $tribu->torneo = collect([
              'puntos' => 0,
              'juegos' => 0,
              'victorias' => 0
            ]);
          }          

          $torneo_puntos = TorneosPuntos::where('id_user',$tribu->id)->where('id_torneo',$torneo->id)->first();
          
          if($torneo_puntos){
            $tribu->torneo['puntos'] = $torneo_puntos->puntos;
            $tribu->torneo['juegos'] = $torneo_puntos->juegos;
            $tribu->torneo['victorias'] = $torneo_puntos->victorias;
          }

          if($ausencias){
            $miembros = UsersMiembros::select('id')->where('id_user',$tribu->id)->pluck('id');          

            $torneo_ausencias = RetosMiembros::where('id_torneo',$torneo->id)->where('puntos',-1)->whereIn('id_miembro',$miembros)->count();

            if($torneo_ausencias){
              $tribu->torneo['ausencias'] = $torneo_ausencias;
            }else{
              $tribu->torneo['ausencias'] = 0;
            }
          }
        }
      }else{
        return $this->respuesta(true, [], 'El torneo no existe', 200);
      }

      $ranking = collect($ranking);

      $sorted = $ranking->sortByDesc(function($item){
          return $item->torneo['puntos'];
      })->values();
      $data = [
        'ranking' => $sorted,
        'ausencias' => $ausencias 
      ];
      return $this->respuesta(true, $data, 'Ranking consultado con éxito', 201);      
    } catch (\Throwable $th) {
      return $this->capturar($th);
    }
  }

  public function actualizarPuntuacion($id, Request $request)
  {
    $data['resultados'] = $request->resultados;
    $data['password'] = $request->password;
    $data['id_torneo'] = $id;
    $validator = Validator::make($data, [          
      'id_torneo' => 'required|exists:torneos,id',
      'resultados' => 'required|array',
      'password' => 'required',
    ]);

    if ($validator->fails()) {
      return $this->respuesta(false, $validator->errors(), 'Error en validación de campos', 400);
    }

    $tribu = User::where('password',$request->password)->first();

    if(!$tribu || $tribu->tipo != 2){
      return $this->respuesta(false, [], 'No tiene permisos de realizar esta acción', 200);
    }

    // Eliminar puntos del torneo
    TorneosPuntos::where('id_torneo',$id)->delete();

    foreach ($data['resultados'] as $resultado) {
      $data_temp = [
        'puntos' => $resultado['torneo']['puntos'],
        'juegos' => $resultado['torneo']['juegos'],
        'victorias' => $resultado['torneo']['victorias'],
        'id_torneo' => $id,
        'id_user' => $resultado['id'],
        'created_by' => $tribu->id,
        'updated_by' => $tribu->id,
      ];

      $datos [] = $data_temp; 
    }
    if(TorneosPuntos::insert($datos)){
      return $this->respuesta(true, [], 'Se ha actualizado la tabla de puntación del torneo de manera exitosa', 201);
    }else{
      return $this->respuesta(false, [], 'Hubo un error actualizando la tabla', 200);
    }
  }

  public function eliminarTorneo($id, Request $request){
    $data = $request->all();
    $data['id'] = $id;
    try {
      $validator = Validator::make($data, [          
        'id' => 'required|exists:retos,id',
        'password' => 'string|required',
      ]);

      if ($validator->fails()) {
        return $this->respuesta(false, $validator->errors(), 'Error en validación de campos', 400);
      }

      $tribu = User::where('password',$request->password)->first();

      if(!$tribu){
        return $this->respuesta(false, [], 'No se encontró la tribu', 200);
      }

      $torneo = Torneos::find($data['id']);

      if($torneo->created_by != $tribu->id){
        return $this->respuesta(false, [], 'No puede eliminar este torneo ya que no lo creó tu tribu', 200);
      }

      $torneo->estado = 0;      

      $torneo->save();

      return $this->respuesta(true, [], 'Torneo eliminado', 201);
      
    } catch (\Throwable $th) {
      return $this->capturar($th);
    }
  }

  public function reportarAusencia($id, Request $request){
    try{
      $data = $request->all();
      $data['id'] = $id;

      $validator = Validator::make($data, [          
        'id' => 'required|exists:torneos,id',
        'id_miembro' => 'required|exists:users_miembros,id',
        'comentario' => 'required|max:300',
        'password' => 'required',
      ]);

      if ($validator->fails()) {
        return $this->respuesta(false, $validator->errors(), 'Error en validación de campos', 400);
      }

      $user = User::where('password',$request->password)->where('tipo',2)->first();

      if(!$user){
        return $this->respuesta(false, [], 'Datos incorrectos o el usuario no es un administrador ', 200);
      }

      $reto_miembro = new RetosMiembros();

      $reto_miembro->puntos = -1;
      $reto_miembro->comentario = $request->comentario;
      $reto_miembro->id_miembro = $request->id_miembro;
      $reto_miembro->id_torneo = $request->id;
      $reto_miembro->created_by = $user->id;

      $reto_miembro->save();

      
      return $this->respuesta(true, [], 'Ausencia reportada con éxito', 201);
    } catch (\Throwable $th) {
     return $this->capturar($th);
    }
  }

  

}
