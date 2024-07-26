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
        Schema::create('Nivel', function (Blueprint $table) {
            $table->bigIncrements('Id_Nivel');
            $table->integer('Numero')->nullable();
            $table->string('Codigo')->nullable();
            $table->string('Nombre')->nullable();
            $table->integer('Valor')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Nivel');
    }
};
