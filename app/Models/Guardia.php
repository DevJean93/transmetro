<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Guardia extends Model
{
    protected $table = 'guardias';

    protected $fillable = ['nombre', 'dpi', 'telefono', 'acceso_id'];

    public function acceso(): BelongsTo
    {
        return $this->belongsTo(Acceso::class);
    }
}
