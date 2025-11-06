<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsuarioMembresia extends Model
{
    use HasFactory;
    protected $table = 'usuario_membresia';
    public $timestamps = false;

    protected $fillable = ['id_usuarios', 'id_membresia', 'fecha_registrado',
        'fecha_inicio', 'fecha_fin', 'nombre', 'precio', 'duracion_dias', 'solvente', 'is_actual'];

    public function abonos()
    {
        return $this->hasMany(MembresiaAbono::class, 'id_usuario_membresia');
    }

    public function usuario() { return $this->belongsTo(Usuario::class, 'id_usuarios'); }
}
