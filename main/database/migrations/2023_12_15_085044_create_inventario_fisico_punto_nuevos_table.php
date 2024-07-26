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
        Schema::create('Inventario_Fisico_Punto_Nuevo', function (Blueprint $table) {
            $table->id('Id_Inventario_Fisico_Punto_Nuevo');
            $table->integer('Funcionario_Autoriza');
            $table->integer('Id_Punto_Dispensacion');
            $table->integer('Id_Grupo_Estiba');
            $table->date('Fecha');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Inventario_Fisico_Punto_Nuevo');
    }
};
