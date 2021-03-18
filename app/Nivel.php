<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Nivel extends Model
{
    

    protected $table = 'niveles';
    protected $primaryKey = 'id';
    protected $perPage = 5;

    protected $fillable = [
        'numero', 'descripcion','activo'
    ];

    //obtener todos los sub niveles de un nivel
    public function subNivel(){
        return $this->hasMany('App\SubNvel');
    }
}
