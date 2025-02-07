<?php

namespace App\Http\Controllers\Util;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UsersMiembros;
use Illuminate\Http\Request;

class UtilController extends Controller
{
    public function tribusSelect(){
      try{
        $tribus = User::select(
          'id',
          'nombre'
        )
        ->where('tipo','!=',2)
        ->orderBy('nombre')
        ->get();
        return $this->respuesta(true, $tribus, 'Tribus encontradas', 200);
      } catch (\Throwable $th) {
       return $this->capturar($th);
      }
    }

    public function integrantesTribuSelect($id){
      try{
        $tribus = UsersMiembros::select(
          'id',
          'nombre'
        )
        ->where('id_user',$id)
        ->orderBy('nombre')
        ->get();
        return $this->respuesta(true, $tribus, 'Integrantes encontrados', 200);
      } catch (\Throwable $th) {
       return $this->capturar($th);
      }
    }
}
