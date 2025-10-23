<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
class Mesa extends Model
{
    //
    use HasFactory;
    protected $fillable = [
        'provincia_id',
        'circuito',
        'establecimiento',
        'electores'
    ];
    public function provincia()
    {
        return $this->belongsTo(Provincia::class);
    }
    public function telegramas()
    {
        return $this->hasMany(Telegrama::class);
    }
}
