<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('Alerta', function (Blueprint $table) {
            $table->id('Id_Alerta');
            $table->integer('Identificacion_Funcionario')->nullable();
            $table->string('Tipo', 30)->nullable();
            $table->timestamp('Fecha')->default(now());
            $table->text('Detalles')->nullable();
            $table->enum('Respuesta', ['Si', 'No'])->default('No');
            $table->integer('Id')->nullable();
            $table->text('Modulo')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alertas');
    }
};
