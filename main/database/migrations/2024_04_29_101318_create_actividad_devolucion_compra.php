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
        Schema::create('Actividad_Devolucion_Compra', function (Blueprint $table) {
            $table->bigIncrements('Id_Actividad_Devolucion_Compra');
            $table->bigInteger('Id_Devolucion_Compra')->nullable();
            $table->bigInteger('Identificacion_Funcionario')->nullable();
            $table->timestamp('Fecha')->useCurrent();
            $table->text('Detalles')->nullable();
            $table->string('Estado', 100)->default('Creacion');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Actividad_Devolucion_Compra');
    }
};
