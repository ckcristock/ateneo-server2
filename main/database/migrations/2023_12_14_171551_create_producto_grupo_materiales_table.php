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
        Schema::create('Producto_Grupo_Materiales', function (Blueprint $table) {
            $table->id('Id_Producto_Grupo_Materiales');
            $table->unsignedBigInteger('Id_Producto')->nullable();
            $table->unsignedBigInteger('Id_Grupo_Materiales')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Producto_Grupo_Materiales');
    }
};
