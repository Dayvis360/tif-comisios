<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Provincia extends Model
{
    protected $fillable = [
        'nombre',
    ];

    /**
     * Relación con las mesas
     */
    public function mesas(): HasMany
    {
        return $this->hasMany(Mesa::class);
    }

    /**
     * Relación con las listas
     */
    public function listas(): HasMany
    {
        return $this->hasMany(Lista::class);
    }
}
