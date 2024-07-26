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
        Schema::create('Paciente_Telefono', function (Blueprint $table) {
            $table->id('Id_Paciente_Telefono');
            $table->string('Id_Paciente', 20);
            $table->string('Numero_Telefono', 10);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Paciente_Telefono');
    }
};
