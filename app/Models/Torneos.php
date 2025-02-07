<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Torneos extends Model
{

  protected $table = "torneos";

  protected $fillable = [
    'estado',
    'titulo',
    'descripcion',
    'fecha',
    'created_by'
  ];
  
  public function tribu()
  {
    return $this->hasOne(User::class, 'id', 'created_by');
  }

  public function comentarios()
  {
    return $this->hasMany(TorneosComentarios::class, 'id_torneo', 'id')->with(['tribu'])->orderBy('created_at', 'DESC');
  }
}
