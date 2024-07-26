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
        Schema::create('Asociacion_Plan_Cuentas', function (Blueprint $table) {
            $table->bigIncrements('Id_Asociacion_Plan_Cuentas');
            $table->integer('Id_Plan_Cuenta');
            $table->integer('Id_Modulo');
            $table->string('Busqueda_Interna', 100);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Asociacion_Plan_Cuentas');
    }
};
