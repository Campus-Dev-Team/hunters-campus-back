<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
//Añadimos la clase JWTSubject 
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Helpers\UtilHelper;

//Añadimos la implementación de JWT en nuestro modelo
class User extends Authenticatable implements JWTSubject {
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'tipo',
        'nombre',
        'color',
        'logo',
        'descripcion',
        'password',
        'correo',     // faltaba esto
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /*
        Añadiremos estos dos métodos
    */
    public function getJWTIdentifier() {
        return $this->getKey();
    }
    public function getJWTCustomClaims() {
        return [];
    }

    /**
     * Obtiene los logos con la ruta absoluta
     *
     * @param String $value
     * @return Array
     */
    public function getLogoAttribute($value)
    {   
        $util_helper = new UtilHelper;
        return ($value) ? $util_helper->temporalUrl($value) : NULL;
    }

    public function lider(){
      return $this->hasOne(UsersMiembros::class, 'id_user', 'id');
    }
}
