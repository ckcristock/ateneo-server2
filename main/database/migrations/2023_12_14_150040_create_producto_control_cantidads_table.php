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
        Schema::create('Producto_Control_Cantidad', function (Blueprint $table) {
            $table->id('Id_Producto_Control_Cantidad');
            $table->foreignId('Id_Producto');
            $table->integer('Cantidad_Minima');
            $table->integer('Cantidad_Presentacion');
            $table->integer('Multiplo');
            $table->timestamp('Fecha_Registro')->default(now());
            $table->datetime('Ultima_Edicion')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Producto_Control_Cantidad');
    }
};
