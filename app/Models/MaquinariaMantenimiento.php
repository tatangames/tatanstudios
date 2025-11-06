<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaquinariaMantenimiento extends Model
{
    use HasFactory;
    protected $table = 'maquinarias_mantenimiento';
    public $timestamps = false;
}
