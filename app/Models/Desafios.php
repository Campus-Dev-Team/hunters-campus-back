<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Desafios extends Model
{
    public $table = 'retos';    

    public function comentarios(){
      return $this->hasMany(RetosComentarios::class, 'id_reto', 'id')->with(['tribu'])->orderBy('created_at','DESC');
    }

    // TRIBUS
    public function tribu1(){
      return $this->hasOne(User::class, 'id', 'id_user_1');
    }

    public function tribu2(){
      return $this->hasOne(User::class, 'id', 'id_user_2');
    }

    public function tribu3(){
      return $this->hasOne(User::class, 'id', 'id_user_3');
    }

    public function tribu4(){
      return $this->hasOne(User::class, 'id', 'id_user_4');
    }

    public function tribu_actualiza(){
      return $this->hasOne(User::class, 'id', 'updated_by')->with(['lider']);
    }
}
