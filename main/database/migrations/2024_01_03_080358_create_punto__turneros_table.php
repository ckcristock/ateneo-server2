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
        Schema::create('Punto_Turnero', function (Blueprint $table) {
            $table->id('Id_Punto_Turnero');
            $table->unsignedBigInteger('Id_Turneros')->nullable();
            $table->unsignedBigInteger('Id_Punto_Dispensacion')->nullable();
            $table->enum('Capita', ['Si', 'No'])->nullable();
            $table->enum('No_Pos', ['Si', 'No'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Punto_Turnero');
    }
};
