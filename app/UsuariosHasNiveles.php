<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UsuariosHasNiveles extends Model
{
    protected $table = 'usuarios_has_niveles';
    protected $primaryKey = 'id';
    protected $perPage = 5;

    protected $fillable = [
        'usuarios_id', 'niveles_id', 'bloqueado'
    ];

    public function nivel(){
        return $this->belongsTo('App\Nivel', 'niveles_id');
    }
}
