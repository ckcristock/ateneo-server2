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
        Schema::create('Producto_Dispensacion', function (Blueprint $table) {
            $table->id('Id_Producto_Dispensacion');
            $table->bigInteger('Id_Dispensacion')->nullable();
            $table->bigInteger('Id_Producto')->nullable();
            $table->bigInteger('Id_Inventario')->nullable();
            $table->bigInteger('Id_Inventario_Nuevo')->nullable();
            $table->decimal('Costo', 50, 0)->nullable();
            $table->string('Cum', 200)->nullable();
            $table->string('Cum_Autorizado', 250)->nullable();
            $table->string('Lote', 200)->nullable();
            $table->longText('Id_Inventario_Nuevo_Seleccionados')->nullable();
            $table->integer('Cantidad_Formulada')->nullable();
            $table->integer('Cantidad_Entregada')->nullable();
            $table->string('Numero_Autorizacion', 200)->nullable();
            $table->string('Fecha_Autorizacion', 100)->nullable();
            $table->string('Numero_Prescripcion', 100)->nullable();
            $table->integer('Entregar_Faltante')->nullable();
            $table->timestamp('Fecha_Carga')->nullable()->default(now());
            $table->integer('Cantidad_Formulada_Total')->nullable();
            $table->integer('Id_Producto_Mipres')->nullable();
            $table->bigInteger('Id_Producto_Dispensacion_Mipres')->nullable();
            $table->integer('Actualizado')->nullable();
            $table->timestamp('Fecha_Actualizado')->nullable();
            $table->tinyInteger('Costo_Actualizado')->nullable();
            $table->tinyInteger('Generico')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Producto_Dispensacion');
    }
};
