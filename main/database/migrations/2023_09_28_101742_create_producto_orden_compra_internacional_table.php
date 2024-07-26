<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductoOrdenCompraInternacionalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Producto_Orden_Compra_Internacional', function (Blueprint $table) {
            $table->bigIncrements('Id_Producto_Orden_Compra_Internacional');
            $table->bigInteger('Id_Orden_Compra_Internacional')->nullable();
            $table->bigInteger('Id_Producto')->nullable();
            $table->decimal('Costo', 30, 6)->nullable()->comment("Este valor esta representado en Dolares(USD)");
            $table->integer('Empaque');
            $table->integer('Cantidad')->nullable();
            $table->double('Subtotal', 20, 6)->nullable();
            $table->integer('Cantidad_Caja');
            $table->integer('Caja_Ancho')->comment("Este valor esta representado en centimetros(cm)");
            $table->integer('Caja_Alto')->comment("Este valor esta representado en centimetros(cm)");
            $table->integer('Caja_Largo')->comment("Este valor esta representado en centimetros(cm)");
            $table->double('Caja_Volumen', 20, 6)->comment("este valor esta representado en metro cubico(m3)");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Producto_Orden_Compra_Internacional');
    }
}
