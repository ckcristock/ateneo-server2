<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductoActaRecepcionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Producto_Acta_Recepcion', function (Blueprint $table) {
            $table->bigIncrements('Id_Producto_Acta_Recepcion');
            $table->integer('Cantidad')->nullable();
            $table->double('Precio', 20, 2)->nullable();
            $table->integer('Impuesto')->nullable();
            $table->double('Subtotal', 20, 2)->nullable();
            $table->string('Lote', 100)->nullable();
            $table->date('Fecha_Vencimiento')->nullable();
            $table->string('Factura', 60)->nullable()->index('Factura_idx');
            $table->bigInteger('Id_Producto')->nullable()->index('Id_Producto');
            $table->bigInteger('Id_Producto_Orden_Compra')->nullable();
            $table->string('Codigo_Compra', 100);
            $table->string('Tipo_Compra', 100);
            $table->bigInteger('Id_Acta_Recepcion')->nullable()->index('Id_Acta_Recepcion_idx');
            $table->decimal('Temperatura', 10, 2)->nullable();
            $table->string('Cumple', 100)->default('Si');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Producto_Acta_Recepcion');
    }
}
