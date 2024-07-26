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
        Schema::create('Resolucion', function (Blueprint $table) {
            $table->id('Id_Resolucion');
            $table->string('Codigo', 50)->nullable();
            $table->string('Nombre', 100)->nullable();
            $table->string('Resolucion', 100)->nullable();
            $table->unsignedBigInteger('Id_Departamento')->nullable();
            $table->date('Fecha_Inicio')->nullable();
            $table->date('Fecha_Fin')->nullable();
            $table->integer('Numero_Inicial')->nullable();
            $table->integer('Numero_Final')->nullable();
            $table->integer('Consecutivo')->nullable();
            $table->text('Descripcion')->nullable();
            $table->string('Modulo', 100)->nullable();
            $table->string('Estado', 45)->default('Activo');
            $table->string('Tipo_Resolucion', 100)->nullable();
            $table->string('Usuario', 500)->nullable();
            $table->string('Pin', 500)->nullable();
            $table->string('Clave_Tecnica', 500)->nullable();
            $table->string('Contrasena', 500)->nullable();
            $table->string('Id_Software', 500)->nullable();
            $table->unsignedBigInteger('resolution_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Resolucion');
    }
};
