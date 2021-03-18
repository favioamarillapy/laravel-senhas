<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class SubNivel extends Model
{
    
    protected $table = 'sub_niveles';
    protected $primaryKey = 'id';
    protected $perPage = 5;

    protected $fillable = [
        'numero', 'descripcion','activo','niveles_id'
    ];

    public function nivel(){
        return $this->belongsTo('App\Nivel', 'niveles_id');
    }
}