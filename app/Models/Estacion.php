<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Estacion extends Model
{
    protected $table = 'estaciones';

    protected $fillable = ['nombre', 'direccion', 'latitud', 'longitud', 'capacidad', 'pasajeros_espera', 'municipalidad_id'];

    public function municipalidad(): BelongsTo
    {
        return $this->belongsTo(Municipalidad::class);
    }

    public function lineas(): BelongsToMany
    {
        return $this->belongsToMany(Linea::class, 'linea_estacion')->withPivot('orden', 'distancia_siguiente_km');
    }

    public function accesos(): HasMany
    {
        return $this->hasMany(Acceso::class);
    }

    public function guardias(): HasManyThrough
    {
        return $this->hasManyThrough(Guardia::class, Acceso::class);
    }

    public function parqueos(): HasMany
    {
        return $this->hasMany(Parqueo::class);
    }

    public function operador(): HasOne
    {
        return $this->hasOne(Operador::class);
    }

    public function alertas(): HasMany
    {
        return $this->hasMany(Alerta::class);
    }

    public function porcentajeOcupacion(): int
    {
        return $this->capacidad > 0 ? (int) round($this->pasajeros_espera * 100 / $this->capacidad) : 0;
    }
}
