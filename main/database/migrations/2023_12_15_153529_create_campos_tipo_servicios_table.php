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
        Schema::create('Campos_Tipo_Servicio', function (Blueprint $table) {
            $table->id('Id_Campos_Tipo_Servicio');
            $table->string('Nombre', 500)->nullable();
            $table->string('Tipo', 500)->nullable();
            $table->enum('Requerido', ['Si', 'No'])->nullable();
            $table->unsignedBigInteger('Id_Tipo_Servicio')->nullable();
            $table->enum('Tipo_Campo', ['Cabecera', 'Producto'])->nullable();
            $table->integer('Longitud')->nullable();
            $table->string('Modulo', 20)->default('Dispensacion');
            $table->enum('Fecha_Formula', ['Si', 'No'])->default('No');
            $table->integer('Dias')->nullable();
            $table->enum('Estado', ['Activo', 'Inactivo'])->default('Activo');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Campos_Tipo_Servicio');
    }
};
