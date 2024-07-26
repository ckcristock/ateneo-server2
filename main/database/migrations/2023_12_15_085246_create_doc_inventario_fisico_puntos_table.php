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
        Schema::create('Doc_Inventario_Fisico_Punto', function (Blueprint $table) {
            $table->id('Id_Doc_Inventario_Fisico_Punto');
            $table->unsignedBigInteger('Id_Estiba')->nullable();
            $table->timestamp('Fecha_Inicio')->nullable();
            $table->datetime('Fecha_Fin')->nullable();
            $table->integer('Funcionario_Digita')->nullable();
            $table->integer('Funcionario_Cuenta')->nullable();
            $table->integer('Funcionario_Autorizo')->nullable();
            $table->longText('Productos_Correctos')->nullable();
            $table->longText('Productos_Diferencia')->nullable();
            $table->text('Observaciones')->nullable();
            $table->string('Estado', 50)->default('Abierto');
            $table->integer('Id_Inventario_Fisico_Punto_Nuevo')->nullable();
            $table->longText('Lista_Productos')->nullable();
            $table->unsignedBigInteger('Funcionario_Anula')->nullable();
            $table->datetime('Fecha_Anulacion')->nullable();
            $table->text('Observaciones_Anulacion')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Doc_Inventario_Fisico_Punto');
    }
};
