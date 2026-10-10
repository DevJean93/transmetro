<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Parqueo extends Model
{
    protected $table = 'parqueos';

    protected $fillable = ['nombre', 'capacidad', 'estacion_id'];

    public function estacion(): BelongsTo
    {
        return $this->belongsTo(Estacion::class);
    }

    public function buses(): HasMany
    {
        return $this->hasMany(Bus::class);
    }
}
