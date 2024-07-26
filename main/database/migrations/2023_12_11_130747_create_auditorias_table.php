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
        Schema::create('Auditoria', function (Blueprint $table) {
            $table->id('Id_Auditoria');
            $table->string('Estado', 100)->default('Pre Auditado');
            $table->string('Funcionario_Preauditoria', 100)->nullable();
            $table->string('Funcionario_Auditoria', 100)->nullable();
            $table->dateTime('Fecha_Preauditoria')->nullable();
            $table->dateTime('Fecha_Auditoria')->nullable();
            $table->bigInteger('Id_Turnero')->nullable();
            $table->bigInteger('Id_Dispensacion')->nullable();
            $table->string('Id_Paciente', 200)->nullable();
            $table->bigInteger('Id_Tipo_Servicio')->nullable();
            $table->string('Nombre_Tipo_Servicio', 100)->nullable();
            $table->integer('Punto_Pre_Auditoria')->nullable();
            $table->string('Estado_Turno', 100)->nullable();
            $table->text('Comentario_Auditor')->nullable();
            $table->string('Archivo', 100)->nullable();
            $table->timestamp('Fecha')->default(now());
            $table->string('Origen', 45)->default('Auditor');
            $table->integer('Dispensador_Preauditoria')->nullable();
            $table->bigInteger('Funcionario_Anula')->nullable();
            $table->dateTime('Fecha_Anulacion')->nullable();
            $table->integer('Id_Servicio')->nullable();
            $table->integer('Id_Dispensacion_Mipres')->nullable();
            $table->integer('Estado_Archivo')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Auditoria');
    }
};
