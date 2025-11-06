<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MembresiaAbono extends Model
{
    use HasFactory;
    protected $table = 'membresia_abono';
    public $timestamps = false;

    protected $fillable = ['id_usuario_membresia', 'fecha_pago', 'monto'];

}
