<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('Inventario_Nuevo', function (Blueprint $table) {
            // Eliminar las claves foráneas existentes
            $table->dropForeign(['Id_Bodega']);
            $table->dropForeign(['Id_Punto_Dispensacion']);
            $table->dropForeign(['Id_Estiba']);

            // Permitir nulos en las columnas
            $table->unsignedBigInteger('Id_Bodega')->nullable()->change();
            $table->unsignedBigInteger('Id_Punto_Dispensacion')->nullable()->change();
            $table->unsignedBigInteger('Id_Estiba')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('Inventario_Nuevo', function (Blueprint $table) {
            // Revertir los cambios permitiendo nulos y eliminando restricciones de clave foránea
            $table->unsignedBigInteger('Id_Bodega')->nullable(false)->change();
            $table->foreign('Id_Bodega')->references('Id_Bodega_Nuevo')->on('Bodega_Nuevo');

            $table->unsignedBigInteger('Id_Punto_Dispensacion')->nullable(false)->change();
            $table->foreign('Id_Punto_Dispensacion')->references('Id_Punto_Dispensacion')->on('Punto_Dispensacion');

            $table->unsignedBigInteger('Id_Estiba')->nullable(false)->change();
            $table->foreign('Id_Estiba')->references('Id_Estiba')->on('Estiba');
        });
    }
};
