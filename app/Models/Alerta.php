<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alerta extends Model
{
    protected $table = 'alertas';

    protected $fillable = ['estacion_id', 'tipo', 'mensaje', 'atendida'];

    protected $casts = ['atendida' => 'boolean'];

    public function estacion(): BelongsTo
    {
        return $this->belongsTo(Estacion::class);
    }
}
