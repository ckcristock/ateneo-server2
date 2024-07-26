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
        Schema::table('Contrato', function (Blueprint $table) {
            $table->unsignedBigInteger('Id_Modalidad_Contratacion_Pago')->nullable();
            $table->unsignedBigInteger('Id_Cobertura_Plan_Beneficios');
            $table->string('Codigo', 255)->nullable();
            $table->enum('Estado', ['Activo', 'Finalizado', 'Inactivo'])->default('Activo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('Contrato', function (Blueprint $table) {
            //
        });
    }
};
