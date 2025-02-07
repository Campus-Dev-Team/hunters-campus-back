<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RetosComentarios extends Model
{
  public $timestamps = false;
  
  public function tribu(){
    return $this->hasOne(User::class, 'id', 'created_by');
  }
}
