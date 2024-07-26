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
        Schema::create('Producto_Nota_Credito', function (Blueprint $table) {
            $table->id('Id_Producto_Nota_Credito');
            $table->unsignedBigInteger('Id_Nota_Credito')->nullable();
            $table->unsignedBigInteger('Id_Inventario')->nullable();
            $table->unsignedBigInteger('Id_Inventario_Nuevo')->nullable();
            $table->integer('Cantidad')->nullable();
            $table->decimal('Precio_Venta', 20, 2)->nullable();
            $table->decimal('Subtotal', 20, 2)->nullable();
            $table->integer('Impuesto')->nullable();
            $table->integer('Id_Producto')->nullable();
            $table->integer('Id_Motivo')->nullable();
            $table->string('Lote', 100)->nullable();
            $table->date('Fecha_Vencimiento')->nullable();
            $table->text('Observacion')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Producto_Nota_Credito');
    }
};
