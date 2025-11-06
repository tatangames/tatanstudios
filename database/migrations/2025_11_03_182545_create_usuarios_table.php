<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * CLIENTES - INFORMACION PERSONAL
     */
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();

            $table->date('fecha_registrado'); // por el sistema
            $table->string('nombre', 100);
            $table->string('imagen', 100)->nullable();

            $table->string('correo', 100)->nullable();
            $table->string('telefono', 25)->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->boolean('sexo')->default(0);

            // en caso de emergencia
            $table->string('emergencia_nombre', 100)->nullable();
            $table->string('emergencia_telefono', 25)->nullable();

            $table->string('condicion_medica',800)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
