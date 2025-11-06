<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * REGISTRO DE MEMBRESIA ASOCIADA
     */
    public function up(): void
    {
        Schema::create('usuario_membresia', function (Blueprint $table) {
            $table->id();

            // Relaciones
            $table->foreignId('id_usuarios')->constrained('usuarios');
            $table->foreignId('id_membresia')->constrained('membresias'); // para reportes

            // Fechas
            $table->date('fecha_registrado'); // fecha en que se registro en el sistema
            $table->date('fecha_inicio');
            $table->date('fecha_fin');

            // Datos de la Membresia
            $table->string('nombre', 100);                 // Ej.: Mensual, Trimestral, Anual
            $table->decimal('precio', 10, 2);         // USD
            $table->unsignedInteger('duracion_dias'); // 30, 90, 365

            // Estado financiero y actual
            $table->boolean('solvente')->default(0);
            $table->boolean('is_actual')->default(0);

            // Índices para consultas rápidas
            $table->index(['id_usuarios', 'is_actual']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuario_membresia');
    }
};
