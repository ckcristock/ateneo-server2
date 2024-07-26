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
        Schema::create('Actividad_No_Conforme_Remision', function (Blueprint $table) {
            $table->bigIncrements('Id_Actividad_No_Conforme_Remision');
            $table->bigInteger('Id_No_Conforme')->nullable();
            $table->bigInteger('Identificacion_Funcionario')->nullable();
            $table->timestamp('Fecha')->useCurrent();
            $table->text('Detalles')->nullable();
            $table->string('Estado', 50)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Actividad_No_Conforme_Remision');
    }
};
