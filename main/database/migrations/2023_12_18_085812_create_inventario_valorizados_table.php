<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('Inventario_Valorizado', function (Blueprint $table) {
            $table->id('Id_Inventario_Valorizado');
            $table->timestamp('Fecha_Documento')->default(now());
            $table->enum('Estado', ['Activo', 'Anulado'])->nullable()->default('Activo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Inventario_Valorizado');
    }
};
