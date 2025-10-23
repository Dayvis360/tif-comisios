<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Testing\Fluent\Concerns\Has;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Provincia extends Model
{
    //
    use HasFactory;
    protected $fillable = [
        'nombre',
    ];
    public function listas()
    {
        return $this->hasMany(Lista::class);
    }
    public function mesas()
    {
        return $this->hasMany(Mesa::class);
    }
}
