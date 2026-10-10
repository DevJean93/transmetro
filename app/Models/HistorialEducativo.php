<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistorialEducativo extends Model
{
    protected $table = 'historial_educativo';

    protected $fillable = ['piloto_id', 'nivel', 'institucion', 'titulo', 'anio'];

    public function piloto(): BelongsTo
    {
        return $this->belongsTo(Piloto::class);
    }
}
