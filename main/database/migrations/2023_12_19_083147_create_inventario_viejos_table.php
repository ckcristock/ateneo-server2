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
        Schema::create('Inventario_Viejo', function (Blueprint $table) {
            $table->id('Id_Inventario');
            $table->string('Codigo')->nullable();
            $table->bigInteger('Id_Producto')->nullable();
            $table->string('Codigo_CUM')->nullable();
            $table->string('Lote')->nullable();
            $table->date('Fecha_Vencimiento')->nullable();
            $table->datetime('Fecha_Carga')->default(now());
            $table->integer('Identificacion_Funcionario')->nullable();
            $table->bigInteger('Id_Bodega')->default(0);
            $table->bigInteger('Id_Punto_Dispensacion')->default(0);
            $table->integer('Cantidad')->nullable();
            $table->integer('Lista_Ganancia')->default(1);
            $table->integer('Id_Dispositivo')->nullable();
            $table->float('Costo')->nullable();
            $table->integer('Cantidad_Apartada')->default(0);
            $table->string('Estiba')->nullable();
            $table->integer('Fila')->nullable();
            $table->text('Alternativo')->nullable();
            $table->string('Actualizado')->default('No');
            $table->integer('Cantidad_Seleccionada')->default(0);
            $table->integer('Cantidad_Leo')->default(0);
            $table->string('Negativo')->nullable();
            $table->integer('Cantidad_Pendientes')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Inventario_Viejo');
    }
};
