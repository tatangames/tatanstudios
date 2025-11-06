<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{
    use HasFactory;
    protected $table = 'usuarios';
    public $timestamps = false;

    public function usuario_membresias()
    {
        return $this->hasMany(UsuarioMembresia::class, 'id_usuarios');
    }
}
