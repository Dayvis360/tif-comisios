<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Candidato extends Model
{
    //
    use HasFactory;
    protected $fillable = [
        'nombre',
        'orden_en_lista',
        'lista_id',
    ];
    public function lista()
    {
        return $this->belongsTo(Lista::class);
    }
}
