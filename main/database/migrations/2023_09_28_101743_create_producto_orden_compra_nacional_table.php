<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductoOrdenCompraNacionalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Producto_Orden_Compra_Nacional', function (Blueprint $table) {
            $table->bigIncrements('Id_Producto_Orden_Compra_Nacional');
            $table->bigInteger('Id_Orden_Compra_Nacional')->nullable();
            $table->bigInteger('Id_Inventario')->nullable();
            $table->bigInteger('Id_Producto')->nullable();
            $table->decimal('Costo', 30, 2)->nullable();
            $table->integer('Cantidad')->nullable();
            $table->integer('Iva')->nullable();
            $table->decimal('Total', 30, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Producto_Orden_Compra_Nacional');
    }
}
