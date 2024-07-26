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
        Schema::create('Dispensacion_Mipres', function (Blueprint $table) {
            $table->id('Id_Dispensacion_Mipres');
            $table->string('Id_Paciente')->nullable();
            $table->date('Fecha_Maxima_Entrega')->nullable();
            $table->integer('Id_Servicio')->nullable();
            $table->integer('Id_Tipo_Servicio')->nullable();
            $table->integer('Numero_Entrega')->nullable();
            $table->datetime('Fecha_Direccionamiento')->nullable();
            $table->datetime('Fecha')->nullable();
            $table->enum('Estado', ['Pendiente', 'Radicado', 'Rechazado', 'Programado', 'Entregado', 'Facturado', 'Radicado Programado'])->default('Pendiente');
            $table->string('Codigo_Municipio')->nullable();
            $table->text('Observaciones')->nullable();
            $table->string('Tipo_Tecnologia')->nullable();
            $table->enum('Bandera', ['Normal', 'Separado'])->default('Normal');
            $table->string('CodEPS', 50)->nullable();
            $table->string('NoIDEPS', 100)->nullable();
            $table->integer('NoSubEntrega')->nullable()->comment('Numero Sub Entrega');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dispensacion_mipres');
    }
};
