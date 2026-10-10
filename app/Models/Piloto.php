<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Piloto extends Model
{
    protected $table = 'pilotos';

    protected $fillable = ['nombre', 'dpi', 'direccion', 'municipio_residencia', 'telefono', 'correo', 'bus_id'];

    public function bus(): BelongsTo
    {
        return $this->belongsTo(Bus::class);
    }

    public function historial(): HasMany
    {
        return $this->hasMany(HistorialEducativo::class)->orderBy('anio');
    }
}
