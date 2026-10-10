<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Bus extends Model
{
    protected $table = 'buses';

    protected $fillable = ['placa', 'modelo', 'capacidad_maxima', 'linea_id', 'parqueo_id'];

    public function linea(): BelongsTo
    {
        return $this->belongsTo(Linea::class);
    }

    public function parqueo(): BelongsTo
    {
        return $this->belongsTo(Parqueo::class);
    }

    public function piloto(): HasOne
    {
        return $this->hasOne(Piloto::class);
    }

    public function visitas(): HasMany
    {
        return $this->hasMany(Visita::class);
    }
}
