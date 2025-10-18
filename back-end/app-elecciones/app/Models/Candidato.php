<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Candidato extends Model
{
    protected $fillable = [
        'lista_id',
        'nombre',
        'orden_en_lista',
    ];

    /**
     * Relación con la lista
     */
    public function lista(): BelongsTo
    {
        return $this->belongsTo(Lista::class);
    }
}
