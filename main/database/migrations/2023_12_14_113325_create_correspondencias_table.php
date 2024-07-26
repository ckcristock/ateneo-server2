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
        Schema::create('Correspondencia', function (Blueprint $table) {
            $table->id('Id_Correspondencia');
            $table->bigInteger('Id_Funcionario_Envia')->nullable();
            $table->dateTime('Fecha_Envio')->nullable();
            $table->integer('Cantidad_Folios')->nullable();
            $table->integer('Punto_Envio')->nullable();
            $table->dateTime('Fecha_Probable_Entrega')->nullable();
            $table->dateTime('Fecha_Entrega_Real')->nullable();
            $table->bigInteger('Id_Funcionario_Recibe')->nullable();
            $table->string('Empresa_Envio', 100)->nullable();
            $table->string('Numero_Guia', 100)->nullable();
            $table->string('Estado', 100)->default('Enviada');
            $table->text('Observaciones_Envio')->nullable();
            $table->text('Observaciones_Recibido')->nullable();
            $table->integer('Id_Regimen')->nullable();
            $table->integer('Id_Tipo_Servicio')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Correspondencia');
    }
};
