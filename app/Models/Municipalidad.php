<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Municipalidad extends Model
{
    protected $table = 'municipalidades';

    protected $fillable = ['nombre', 'departamento'];

    public function lineas(): HasMany
    {
        return $this->hasMany(Linea::class);
    }

    public function estaciones(): HasMany
    {
        return $this->hasMany(Estacion::class);
    }
}
