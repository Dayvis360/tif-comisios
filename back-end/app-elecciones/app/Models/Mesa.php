<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mesa extends Model
{
    protected $fillable = [
        'provincia_id',
        'circuito',
        'establecimiento',
        'electores',
    ];

    /**
     * Relación con la provincia
     */
    public function provincia(): BelongsTo
    {
        return $this->belongsTo(Provincia::class);
    }
}
