<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('Producto_Inventario_Fisico_Punto', function (Blueprint $table) {
            $table->id('Id_Producto_Inventario_Fisico');
            $table->unsignedBigInteger('Id_Producto')->nullable();
            $table->string('Primer_Conteo', 50)->default('0');
            $table->date('Fecha_Primer_Conteo')->nullable();
            $table->integer('Segundo_Conteo')->default(0);
            $table->date('Fecha_Segundo_Conteo')->nullable();
            $table->unsignedBigInteger('Id_Inventario_Fisico_Punto_Nuevo')->nullable();
            $table->string('Lote', 100)->nullable();
            $table->date('Fecha_Vencimiento')->nullable();
            $table->string('Actualizado', 100)->nullable();
            $table->integer('Diferencia')->default(0);
            $table->integer('Cantidad_Final')->nullable();
            $table->integer('Id_Inventario')->nullable();
            $table->unsignedBigInteger('Id_Inventario_Nuevo')->nullable();
            $table->integer('Cantidad_Inventario')->nullable();

            $table->foreign('Id_Inventario_Fisico_Punto_Nuevo')
                ->references('Id_Inventario_Fisico_Punto_Nuevo')->on('Inventario_Fisico_Punto_Nuevo')
                ->name('fk_inventario_punto'); 

            $table->index('Id_Inventario_Fisico_Punto_Nuevo', 'idx_inventario_punto'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Producto_Inventario_Fisico_Punto');
    }
};
