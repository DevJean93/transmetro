<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Operador extends Model
{
    protected $table = 'operadores';

    protected $fillable = ['user_id', 'estacion_id', 'telefono'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function estacion(): BelongsTo
    {
        return $this->belongsTo(Estacion::class);
    }
}
