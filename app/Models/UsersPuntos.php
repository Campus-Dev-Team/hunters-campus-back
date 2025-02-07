<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsersPuntos extends Model {
    
    protected $table = "users_puntos";
    const UPDATED_AT = null;

    public function tribu(){
      return $this->hasOne(User::class, 'id', 'id_user')->select('id','nombre','color')->with(['lider']);
    }
}
