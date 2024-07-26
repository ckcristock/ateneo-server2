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
            // Cambiar la columna id_Bodega_Nuevo para que no admita valores nulos y agregar restricción de clave foránea
            $table->foreign('id_Bodega_Nuevo')->references('Id_Bodega_Nuevo')->on('Bodega_Nuevo');
            $table->unsignedBigInteger('id_Bodega_Nuevo')->nullable(false)->change();

            // Cambiar la columna Id_Punto_Dispensacion para que no admita valores nulos y agregar restricción de clave foránea
            $table->unsignedBigInteger('Id_Punto_Dispensacion')->nullable(false)->change();
            $table->foreign('Id_Punto_Dispensacion')->references('Id_Punto_Dispensacion')->on('Punto_Dispensacion');

            // Cambiar la columna Id_Remision para que no admita valores nulos y agregar restricción de clave foránea
            $table->unsignedBigInteger('Id_Remision')->nullable(false)->change();
            $table->foreign('Id_Remision')->references('Id_Remision')->on('Remision');

            // Cambiar la columna company_id para que no admita valores nulos y agregar restricción de clave foránea
            $table->unsignedBigInteger('company_id')->nullable(false)->change();
            $table->foreign('company_id')->references('id')->on('companies');

            $table->unsignedBigInteger('Identificacion_Funcionario')->nullable(false)->change();
            $table->foreign('Identificacion_Funcionario')->references('identifier')->on('people');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('Acta_Recepcion_Remision', function (Blueprint $table) {
            $table->dropForeign(['id_Bodega_Nuevo']);
            $table->dropForeign(['Id_Punto_Dispensacion']);
            $table->dropForeign(['Id_Remision']);
            $table->dropForeign(['company_id']);
            $table->dropForeign(['Identificacion_Funcionario']);
        });
    }
};
