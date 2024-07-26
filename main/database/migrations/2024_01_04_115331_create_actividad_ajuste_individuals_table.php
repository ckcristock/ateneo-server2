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
        Schema::create('Actividad_Ajuste_Individual', function (Blueprint $table) {
            $table->id('Id_Actividad_Ajuste_Individual');
            $table->unsignedBigInteger('Id_Ajuste_Individual')->nullable();
            $table->unsignedBigInteger('Identificacion_Funcionario')->nullable();
            $table->string('Detalle', 255)->nullable();
            $table->string('Estado', 255)->nullable();
            $table->timestamp('Fecha_Creacion')->nullable()->default(now());
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Actividad_Ajuste_Individual');
    }
};
