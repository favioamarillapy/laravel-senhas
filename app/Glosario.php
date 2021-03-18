<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Glosario extends Model
{
    protected $table = 'glosario';
    protected $primaryKey = 'id';
    protected $perPage = 5;

    protected $fillable = [
        'descripcion', 'imagen','activo'
    ];
}