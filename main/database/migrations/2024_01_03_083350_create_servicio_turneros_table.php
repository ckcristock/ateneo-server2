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
        Schema::create('Servicio_Turnero', function (Blueprint $table) {
            $table->id('Id_Servicio_Turnero');
            $table->unsignedBigInteger('Id_Turnero');
            $table->unsignedBigInteger('Id_Servicio');
            $table->dateTime('Fecha_Registro')->default(now());
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Servicio_Turnero');
    }
};
