<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UsuariosHasSubNiveles extends Model
{
    protected $table = 'usuarios_has_sub_niveles';
    protected $primaryKey = 'id';
    protected $perPage = 5;

    protected $fillable = [
        'usuarios_id', 'sub_niveles_id', 'bloqueado', 'intentos', 'puntos'
    ];

    public function subNivel(){
        return $this->belongsTo('App\SubNivel', 'sub_niveles_id');
    }
}
