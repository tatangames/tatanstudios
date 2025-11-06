<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * REGISTRO DE MAQUINARIA
     */
    public function up(): void
    {
        Schema::create('maquinarias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_maqui_categoria')->constrained('categoria_maquinaria');
            $table->date('fecha');
            $table->string('nombre', 100);
            $table->decimal('precio', 10, 2);
            $table->string('descripcion', 500)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maquinarias');
    }
};
