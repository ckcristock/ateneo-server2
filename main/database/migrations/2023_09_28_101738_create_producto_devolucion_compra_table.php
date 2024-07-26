<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductoDevolucionCompraTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Producto_Devolucion_Compra', function (Blueprint $table) {
            $table->bigIncrements('Id_Producto_Devolucion_Compra');
            $table->bigInteger('Id_Producto')->nullable();
            $table->integer('Id_Inventario')->nullable();
            $table->integer('Id_Inventario_Nuevo')->nullable();
            $table->string('Lote', 60)->nullable();
            $table->date('Fecha_Vencimiento')->nullable();
            $table->bigInteger('Id_Devolucion_Compra')->nullable();
            $table->integer('Cantidad')->nullable();
            $table->text('Motivo')->nullable();
            $table->double('Costo')->nullable();
            $table->integer('Impuesto')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Producto_Devolucion_Compra');
    }
}
