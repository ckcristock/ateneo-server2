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


        Schema::table('Orden_Compra_Nacional', function (Blueprint $table) {
            $table->dropForeign(['Id_Bodega_Nuevo']);
        });

        // Modificar la columna para permitir valores nulos
        Schema::table('Orden_Compra_Nacional', function (Blueprint $table) {
            $table->unsignedBigInteger('Id_Bodega_Nuevo')->nullable()->change();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        // Modificar la columna para no permitir valores nulos
        Schema::table('Orden_Compra_Nacional', function (Blueprint $table) {
            $table->unsignedBigInteger('Id_Bodega_Nuevo')->nullable(false)->change();
        });

        // Volver a agregar la restricción de clave externa
        Schema::table('Orden_Compra_Nacional', function (Blueprint $table) {
            $table->foreign('Id_Bodega_Nuevo')->references('Id_Bodega_Nuevo')->on('Bodega_Nuevo');
        });
    }
};
