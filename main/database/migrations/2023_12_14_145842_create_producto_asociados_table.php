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
        Schema::create('Producto_Asociado', function (Blueprint $table) {
            $table->id('Id_Producto_Asociado');
            $table->text('Producto_Asociado');
            $table->text('Asociados2')->nullable();
            $table->timestamp('Fecha_Registro')->default(now());
            $table->string('Id_Asociado_Genericos', 250)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Producto_Asociado');
    }
};
