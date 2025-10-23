<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Lista extends Model
{
    //
    use HasFactory;
    protected $fillable = [
        'nombre',
        'alianza',
        'cargo',
        'provincia_id',
    ];
    public function provincia()
    {
        return $this->belongsTo(Provincia::class);
    }
    public function candidatos()
    {
        return $this->hasMany(Candidato::class);
    }
    public function telegramas()
    {
        return $this->hasMany(Telegrama::class);
    }
}
