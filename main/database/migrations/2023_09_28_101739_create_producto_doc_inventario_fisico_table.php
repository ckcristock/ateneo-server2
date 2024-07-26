<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductoDocInventarioFisicoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Producto_Doc_Inventario_Fisico', function (Blueprint $table) {
            $table->bigIncrements('Id_Producto_Doc_Inventario_Fisico');
            $table->bigInteger('Id_Producto')->nullable();
            $table->bigInteger('Id_Inventario_Nuevo')->nullable();
            $table->integer('Primer_Conteo')->nullable();
            $table->date('Fecha_Primer_Conteo')->nullable();
            $table->integer('Segundo_Conteo')->nullable();
            $table->date('Fecha_Segundo_Conteo')->nullable();
            $table->integer('Cantidad_Auditada')->nullable();
            $table->integer('Funcionario_Cantidad_Auditada')->nullable();
            $table->integer('Cantidad_Inventario')->nullable();
            $table->bigInteger('Id_Doc_Inventario_Fisico')->nullable();
            $table->string('Lote', 100)->nullable();
            $table->date('Fecha_Vencimiento')->nullable();
            $table->string('Actualizado', 100)->default('Pendiente');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Producto_Doc_Inventario_Fisico');
    }
}
