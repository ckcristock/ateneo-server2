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
        Schema::create('Turneros', function (Blueprint $table) {
            $table->id('Id_Turneros');
            $table->string('Nombre', 100)->nullable();
            $table->string('Direccion', 500)->nullable();
            $table->enum('Capita', ['Si', 'No'])->default('Si');
            $table->enum('No_Pos', ['Si', 'No'])->default('No');
            $table->enum('Autorizacion_Servicios', ['Si', 'No'])->default('No');
            $table->integer('Maximo_Turnos')->default(100);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Turneros');
    }
};
