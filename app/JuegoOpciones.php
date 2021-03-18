<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class JuegoOpciones extends Model
{
    protected $table = 'juego_opciones';
    protected $primaryKey = 'id';
    protected $perPage = 5;

    protected $fillable = [
        'descripcion', 'correcto', 'juegos_id'
    ];

    public function juego(){
        return $this->belongsTo('App\Juego', 'juegos_id');
    }
}
