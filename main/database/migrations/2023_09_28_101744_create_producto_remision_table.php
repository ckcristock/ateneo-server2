<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductoRemisionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Producto_Remision', function (Blueprint $table) {
            $table->bigIncrements('Id_Producto_Remision');
            $table->bigInteger('Id_Remision')->nullable()->index('Id_Remision_idx');
            $table->bigInteger('Id_Producto_Factura_Venta')->nullable();
            $table->integer('Id_Inventario')->nullable();
            $table->string('Lote', 100)->nullable();
            $table->date('Fecha_Vencimiento')->nullable();
            $table->integer('Cantidad')->nullable();
            $table->bigInteger('Id_Producto')->nullable()->index('Id_Producto');
            $table->text('Nombre_Producto')->nullable();
            $table->integer('Cantidad_Total')->nullable();
            $table->decimal('Precio', 50, 2)->nullable();
            $table->decimal('Costo', 50, 2)->nullable();
            $table->integer('Descuento')->nullable();
            $table->integer('Impuesto')->nullable();
            $table->decimal('Subtotal', 50, 2)->nullable();
            $table->decimal('Total_Descuento', 50, 2)->default(0.00);
            $table->decimal('Total_Impuesto', 50, 2)->default(0.00);
            $table->bigInteger('Id_Inventario_Nuevo')->nullable();
            $table->tinyInteger('Costo_Actualizado')->nullable()->comment("actualizacion de costos masivos ");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Producto_Remision');
    }
}
