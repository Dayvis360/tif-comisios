<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lista extends Model
{
    protected $fillable = [
        'provincia_id',
        'cargo',
        'nombre_lista',
        'alianza',
    ];

    /**
     * Relación con la provincia
     */
    public function provincia(): BelongsTo
    {
        return $this->belongsTo(Provincia::class);
    }

    /**
     * Relación con los candidatos
     */
    public function candidatos(): HasMany
    {
        return $this->hasMany(Candidato::class);
    }
}
