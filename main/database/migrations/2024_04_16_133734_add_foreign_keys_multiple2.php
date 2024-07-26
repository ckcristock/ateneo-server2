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
        Schema::table('Acta_Recepcion', function (Blueprint $table) {
            // Cambiar la columna id_Bodega_Nuevo para que no admita valores nulos y agregar restricción de clave foránea
            $table->unsignedBigInteger('id_Bodega_Nuevo')->nullable(true)->change();
            $table->foreign('id_Bodega_Nuevo')->references('Id_Bodega_Nuevo')->on('Bodega_Nuevo');
           
            $table->unsignedBigInteger('Identificacion_Funcionario')->nullable(true)->change();
            $table->foreign('Identificacion_Funcionario')->references('id')->on('people');

            $table->unsignedBigInteger('Id_Proveedor')->nullable(true)->change();
            $table->foreign('Id_Proveedor')->references('id')->on('third_parties');
            
            // Cambiar la columna Id_Remision para que no admita valores nulos y agregar restricción de clave foránea
            $table->unsignedBigInteger('Id_Orden_Compra_Nacional')->nullable(true)->change();
            $table->foreign('Id_Orden_Compra_Nacional')->references('Id_Orden_Compra_Nacional')->on('Orden_Compra_Nacional');

            $table->unsignedBigInteger('Id_Orden_Compra_Internacional')->nullable(true)->change();
            $table->foreign('Id_Orden_Compra_Internacional')->references('Id_Orden_Compra_Internacional')->on('Orden_Compra_Internacional');

            // Cambiar la columna company_id para que no admita valores nulos y agregar restricción de clave foránea
            $table->unsignedBigInteger('company_id')->nullable(true)->change();
            $table->foreign('company_id')->references('id')->on('companies');

            $table->unsignedBigInteger('Id_Causal_Anulacion')->nullable(true)->change();
            $table->foreign('Id_Causal_Anulacion')->references('Id_Causal_Anulacion')->on('Causal_Anulacion');

        });

        Schema::table('Factura_Acta_Recepcion', function (Blueprint $table) {
            $table->unsignedBigInteger('Id_Acta_Recepcion')->nullable(true)->change();
            $table->foreign('Id_Acta_Recepcion')->references('Id_Acta_Recepcion')->on('Acta_Recepcion');

            $table->unsignedBigInteger('Id_Orden_Compra')->nullable(true)->change();
            $table->foreign('Id_Orden_Compra')->references('Id_Orden_Compra_Nacional')->on('Orden_Compra_Nacional');
        });

        Schema::table('Producto_Acta_Recepcion', function (Blueprint $table) {
            $table->unsignedBigInteger('Factura')->nullable(true)->change();
            $table->foreign('Factura')->references('Id_Factura_Acta_Recepcion')->on('Factura_Acta_Recepcion');

            $table->unsignedBigInteger('Id_Producto')->nullable(true)->change();
            $table->foreign('Id_Producto')->references('Id_Producto')->on('Producto');

            $table->unsignedBigInteger('Id_Acta_Recepcion')->nullable(true)->change();
            $table->foreign('Id_Acta_Recepcion')->references('Id_Acta_Recepcion')->on('Acta_Recepcion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('Acta_Recepcion', function (Blueprint $table) {
            $table->dropForeign(['id_Bodega_Nuevo']);
            $table->dropForeign(['Identificacion_Funcionario']);
            $table->dropForeign(['Id_Proveedor']);
            $table->dropForeign(['Id_Orden_Compra_Nacional']);
            $table->dropForeign(['Id_Orden_Compra_Internacional']);
            $table->dropForeign(['company_id']);
            $table->dropForeign(['Id_Causal_Anulacion']);
        });

        Schema::table('Factura_Acta_Recepcion', function (Blueprint $table) {
            $table->dropForeign(['Id_Acta_Recepcion']);
            $table->dropForeign(['Id_Orden_Compra']);
        });

        Schema::table('Producto_Acta_Recepcion', function (Blueprint $table) {
            $table->dropForeign(['Factura']);
            $table->dropForeign(['Id_Producto']);
            $table->dropForeign(['Id_Acta_Recepcion']);
        });
    }
};
