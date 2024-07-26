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
        Schema::create('Departamento_Cliente', function (Blueprint $table) {
            $table->id('Id_Departamento_Cliente');
            $table->unsignedBigInteger('Id_Departamento');
            $table->unsignedBigInteger('Id_Cliente')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Departamento_Cliente');
    }
};
