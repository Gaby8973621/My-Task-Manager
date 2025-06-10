<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tarea extends Model
{
    use HasFactory;


    protected $fillable = [
        'titulo',
        'descripcion',
        'completada',
        'user_id',
    ];

    /**
     * Relación con el usuario que creó la tarea.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
