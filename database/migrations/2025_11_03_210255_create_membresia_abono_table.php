<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ABONOS DE MEMBRESIA
     */
    public function up(): void
    {
        Schema::create('membresia_abono', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_usuario_membresia')->constrained('usuario_membresia');

            $table->date('fecha_pago'); // fecha pagada
            $table->decimal('monto', 10, 2); // monto pagado
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membresia_abono');
    }
};
