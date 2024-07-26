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
        Schema::create('Producto_Doc_Inventario_Fisico_Punto', function (Blueprint $table) {
            $table->id('Id_Producto_Doc_Inventario_Fisico_Punto');
            $table->unsignedBigInteger('Id_Producto')->nullable();
            $table->unsignedBigInteger('Id_Inventario_Nuevo')->nullable();
            $table->integer('Primer_Conteo')->nullable();
            $table->date('Fecha_Primer_Conteo')->nullable();
            $table->integer('Segundo_Conteo')->nullable();
            $table->date('Fecha_Segundo_Conteo')->nullable();
            $table->integer('Cantidad_Auditada')->nullable();
            $table->integer('Funcionario_Cantidad_Auditada')->nullable();
            $table->integer('Cantidad_Inventario')->nullable();
            $table->unsignedBigInteger('Id_Doc_Inventario_Fisico_Punto')->nullable();
            $table->string('Lote', 100)->nullable();
            $table->date('Fecha_Vencimiento')->nullable();
            $table->string('Actualizado', 100)->default('Pendiente');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Producto_Doc_Inventario_Fisico_Punto');
    }
};
