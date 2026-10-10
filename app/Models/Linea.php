<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Linea extends Model
{
    protected $table = 'lineas';

    protected $fillable = ['nombre', 'color', 'municipalidad_id'];

    public function municipalidad(): BelongsTo
    {
        return $this->belongsTo(Municipalidad::class);
    }

    public function estaciones(): BelongsToMany
    {
        return $this->belongsToMany(Estacion::class, 'linea_estacion')
            ->withPivot('orden', 'distancia_siguiente_km')
            ->orderByPivot('orden');
    }

    public function buses(): HasMany
    {
        return $this->hasMany(Bus::class);
    }

    // Req. 13: distancia total de la línea
    public function distanciaTotal(): float
    {
        return (float) $this->estaciones->sum('pivot.distancia_siguiente_km');
    }

    // Req. 5: mínimo = n estaciones, máximo = 2n
    public function busesMinimos(): int
    {
        return $this->estaciones()->count();
    }

    public function busesMaximos(): int
    {
        return $this->busesMinimos() * 2;
    }
}
