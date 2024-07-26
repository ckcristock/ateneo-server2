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
        Schema::table('Acta_Recepcion_Remision', function (Blueprint $table) {
            // Eliminar las claves foráneas existentes
            $table->dropForeign(['id_Bodega_Nuevo']);
            $table->dropForeign(['Id_Punto_Dispensacion']);

            // Permitir nulos en las columnas
            $table->unsignedBigInteger('id_Bodega_Nuevo')->nullable()->change();
            $table->unsignedBigInteger('Id_Punto_Dispensacion')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('Acta_Recepcion_Remision', function (Blueprint $table) {
            // Revertir los cambios permitiendo nulos y eliminando restricciones de clave foránea
            $table->unsignedBigInteger('id_Bodega_Nuevo')->nullable(false)->change();
            $table->foreign('id_Bodega_Nuevo')->references('Id_Bodega_Nuevo')->on('Bodega_Nuevo');
            
            $table->unsignedBigInteger('Id_Punto_Dispensacion')->nullable(false)->change();
            $table->foreign('Id_Punto_Dispensacion')->references('Id_Punto_Dispensacion')->on('Punto_Dispensacion');
        });
    }
};
