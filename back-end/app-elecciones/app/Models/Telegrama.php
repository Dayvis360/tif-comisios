<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use
Illuminate\Database\Eloquent\Factories\HasFactory;
class Telegrama extends Model
{
    //
    use HasFactory;
    protected $fillable = [
        'mesa_id',
        'lista_id',
        'voto_Diputados',
        'voto_Senadores',
        'voto_Blancos',
        'voto_Nulos',
        'voto_Recurridos'
    ];
    public function mesa()
    {
        return $this->belongsTo(Mesa::class);
    }
    public function lista()
    {
        return $this->belongsTo(Lista::class);
    }
}
