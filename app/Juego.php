<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Juego extends Model
{
    protected $table = 'juegos';
    protected $primaryKey = 'id';
    protected $perPage = 5;

    protected $fillable = [
        'nombre', 'imagen', 'sub_niveles_id', 'activo'
    ];

    public function subNivel(){
        return $this->belongsTo('App\SubNivel', 'sub_niveles_id');
    }

    public function opciones(){
        return $this->hasMany('App\JuegoOpciones', 'juegos_id');
    }
}
