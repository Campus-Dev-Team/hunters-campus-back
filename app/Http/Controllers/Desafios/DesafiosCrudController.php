<?php

namespace App\Http\Controllers\Desafios;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Desafios;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\RetosComentarios;
use Illuminate\Support\Facades\Validator;

class DesafiosCrudController extends Controller
{
  public function mostrar($id)
  {
    try {
      $desafio = Desafios::with(['comentarios','tribu_actualiza'])->find($id);

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
    } catch (\Throwable $th) {
      return $this->capturar($th);
    }
  }

  public function crearComentario(Request $request){
    try {

        $validator = Validator::make($request->all(), [          
          'password' => 'string|required',
          'id_reto' => 'required|exists:retos,id',
          'comentario' => 'required|max:500',
        ]);

        if ($validator->fails()) {
          return $this->respuesta(false, $validator->errors(), 'Error en validación de campos', 400);
        }

        $tribu = User::where('password',$request->password)->first();

        if(!$tribu){
          return $this->respuesta(false, [], 'No se encontró la tribu', 200);
        }

        $comentario = new RetosComentarios();

        $comentario->comentario = $request->comentario;
        $comentario->id_reto = $request->id_reto;
        $comentario->created_by = $tribu->id;

        $comentario->save();

        return $this->respuesta(true, $comentario, 'Comentario realizado con éxito', 201);
        


    } catch (\Throwable $th) {
      return $this->capturar($th);
    }
  }

  public function aceptarDesafio($id, Request $request){
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

      $desafio = Desafios::find($data['id']);
      
      // Si quien aceptará el reto es la misma tribu que lo ha creado
      if($tribu->id == $desafio->created_by){
        return $this->respuesta(false, [], 'No puedes aceptar un reto que tu tribu ha creado', 200);
      }

      // Si ya se ha aceptado el reto, no puedes volver a aceptarlo
      if($tribu->id == $desafio->id_user_2 || $tribu->id == $desafio->id_user_3 || $tribu->id == $desafio->id_user_4){
        return $this->respuesta(false, [], 'No puedes aceptar un reto que ya has aceptado', 200);
      }

      if($desafio->id_user_2 == NULL){
        $desafio->id_user_2 = $tribu->id;
      }elseif ($desafio->id_user_3 == NULL && $desafio->cantidad >=3 ) {
        $desafio->id_user_3 = $tribu->id;
      }elseif ($desafio->id_user_4 == NULL && $desafio->cantidad ==4 ) {
        $desafio->id_user_4 = $tribu->id;
      }else{
        return $this->respuesta(false, [], 'Desafío completo. Busca otro desafío donde haya espacio para tu tribu ', 200);
      }


      // evaluar para cambiar el estado
      if($desafio->cantidad == 2 && $desafio->id_user_2 != NULL){
        $desafio->estado = 2;
      }elseif ($desafio->cantidad == 3 && $desafio->id_user_2 != NULL && $desafio->id_user_3 != NULL) {
        $desafio->estado = 2;
      }elseif ($desafio->cantidad == 4 && $desafio->id_user_2 != NULL && $desafio->id_user_3 != NULL && $desafio->id_user_4 != NULL) {
        $desafio->estado = 2;
      }

      $desafio->save();

      return $this->respuesta(true, $desafio, 'Reto aceptado', 201);
      
    } catch (\Throwable $th) {
      return $this->capturar($th);
    }   

  }

  public function eliminarDesafio($id, Request $request){
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

      $desafio = Desafios::find($data['id']);

      if($desafio->created_by != $tribu->id){
        return $this->respuesta(false, [], 'No puede eliminar este desafio ya que no lo creó tu tribu', 200);
      }

      if($desafio->estado == 1){
        $desafio->estado = 11;
      }elseif ($desafio->estado == 2) {
        $desafio->estado = 12;
      }else{
        return $this->respuesta(false, [], 'No puede eliminar este desafio ya que en su estado actual el proceso no puede realizar', 200);
      }

      $desafio->save();

      return $this->respuesta(true, [], 'Reto eliminado', 201);
      
    } catch (\Throwable $th) {
      return $this->capturar($th);
    }
  }


}
