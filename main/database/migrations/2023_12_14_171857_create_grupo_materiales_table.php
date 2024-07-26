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
        Schema::create('Grupo_Materiales', function (Blueprint $table) {
            $table->id('Id_Grupo_Materiales');
            $table->integer('Codigo')->nullable();
            $table->string('Nombre', 100)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Grupo_Materiales');
    }
};
