<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('Producto_Factura_Venta', function (Blueprint $table) {
            $table->id('Id_Producto_Factura_Venta');
            $table->bigInteger('Id_Factura_Venta')->nullable();
            $table->bigInteger('Id_Inventario')->nullable();
            $table->bigInteger('Id_Inventario_Nuevo')->nullable();
            $table->bigInteger('Id_Producto')->nullable();
            $table->string('Lote', 200)->nullable();
            $table->date('Fecha_Vencimiento')->nullable();
            $table->integer('Cantidad')->nullable();
            $table->decimal('Precio_Venta', 20, 2)->nullable();
            $table->decimal('Descuento', 50, 2)->default(0.00);
            $table->integer('Impuesto')->nullable();
            $table->decimal('Subtotal', 20, 2)->nullable();
            $table->bigInteger('Id_Remision')->nullable();
            $table->string('Invima', 100)->nullable();
            $table->longText('producto');
        });
    }

    public function down()
    {
        Schema::dropIfExists('Producto_Factura_Venta');
    }
};
