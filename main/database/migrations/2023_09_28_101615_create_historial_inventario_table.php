<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistorialInventarioTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Historial_Inventario', function (Blueprint $table) {
            $table->bigIncrements('Id_Historial_Inventario');
            $table->bigInteger('Id_Inventario_Nuevo')->nullable();
            $table->bigInteger('Id_Estiba')->nullable();
            $table->string('Codigo_CUM', 100)->nullable();
            $table->string('Lote', 200)->nullable();
            $table->dateTime('Fecha_Vencimiento')->nullable();
            $table->integer('Cantidad')->nullable();
            $table->integer('Cantidad_Apartada')->nullable();
            $table->integer('Cantidad_Seleccionada')->nullable();
            $table->bigInteger('Id_Doc_Inventario_Fisico')->nullable();
            $table->bigInteger('Id_Producto')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Historial_Inventario');
    }
}
