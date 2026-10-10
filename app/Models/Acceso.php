<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Acceso extends Model
{
    protected $table = 'accesos';

    protected $fillable = ['estacion_id', 'nombre'];

    public function estacion(): BelongsTo
    {
        return $this->belongsTo(Estacion::class);
    }

    public function guardias(): HasMany
    {
        return $this->hasMany(Guardia::class);
    }
}
