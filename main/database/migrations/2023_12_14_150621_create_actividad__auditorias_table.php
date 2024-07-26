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
        Schema::create('Actividad_Auditoria', function (Blueprint $table) {
            $table->id('Id_Actividad_Auditoria');
            $table->foreignId('Id_Auditoria')->nullable();
            $table->integer('Identificacion_Funcionario')->nullable();
            $table->string('Estado', 50)->nullable();
            $table->text('Detalle')->nullable();
            $table->timestamp('Fecha')->default(now());
            $table->text('Observacion')->nullable();
            $table->text('Errores')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Actividad_Auditoria');
    }
};
