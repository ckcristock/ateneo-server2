<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('Producto_Acta_Recepcion_Internacional', function (Blueprint $table) {
            $table->id('Id_Producto_Acta_Recepcion_Internacional');
            $table->unsignedBigInteger('Id_Producto_Orden_Compra_Internacional');
            $table->unsignedBigInteger('Id_Producto');
            $table->unsignedBigInteger('Id_Acta_Recepcion_Internacional');
            $table->integer('Cantidad');
            $table->decimal('Precio', 30, 6);
            $table->decimal('Impuesto', 30, 6);
            $table->decimal('Subtotal', 30, 6);
            $table->string('Lote', 30);
            $table->date('Fecha_Vencimiento');
            $table->string('Factura', 25);
            $table->string('Codigo_Compra', 20);
            $table->text('Archivo_Producto')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Producto_Acta_Recepcion_Internacional');
    }
};
