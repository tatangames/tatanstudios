<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * CONTROL DE MANTENIMIENTO DE MAQUINARIA / GASTOS
     */
    public function up(): void
    {
        Schema::create('maquinarias_mantenimiento', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_maquinarias')->constrained('maquinarias');
            $table->date('fecha');
            $table->string('descripcion', 500)->nullable();
            $table->decimal('precio', 10, 2);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maquinarias_mantenimiento');
    }
};
