<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class CreateProductoFacturaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Producto_Factura', function (Blueprint $table) {
            $table->bigIncrements('Id_Producto_Factura');
            $table->bigInteger('Id_Factura')->nullable();
            $table->bigInteger('Id_Inventario')->nullable();
            $table->integer('Id_Producto_Dispensacion')->nullable();
            $table->integer('Id_Producto')->nullable();
            $table->string('Lote', 45)->nullable();
            $table->string('Fecha_Vencimiento', 45)->nullable();
            $table->integer('Cantidad')->nullable();
            $table->decimal('Precio', 20)->nullable();
            $table->decimal('Descuento', 20, 4)->nullable();
            $table->float('Impuesto', 10, 0)->nullable();
            $table->decimal('Subtotal', 20)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Producto_Factura');
    }
}
;
