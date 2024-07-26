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
        Schema::create('Producto_Acta_Recepcion_Remision', function (Blueprint $table) {
            $table->id('Id_Producto_Acta_Recepcion_Remision');
            $table->bigInteger('Id_Acta_Recepcion_Remision')->nullable();
            $table->bigInteger('Id_Producto')->nullable();
            $table->string('Lote', 100)->nullable();
            $table->integer('Cantidad')->nullable();
            $table->enum('Cumple', ['Si', 'No'])->default('Si');
            $table->enum('Revisado', ['Si', 'No'])->default('Si');
            $table->bigInteger('Id_Producto_Remision')->nullable();
            $table->date('Fecha_Vencimiento')->nullable();
            $table->bigInteger('Id_Remision')->nullable();
            $table->string('Temperatura', 100)->default('No');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Producto_Acta_Recepcion_Remision');
    }
};
