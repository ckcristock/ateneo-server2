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
        Schema::table('Lista_Ganancia', function (Blueprint $table) {
            Schema::dropIfExists('Lista_Ganancia');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('Lista_Ganancia', function (Blueprint $table) {
            $table->bigIncrements('Id_Lista_Ganancia');
            $table->string('Codigo', 100)->nullable()->collation('utf8mb4_unicode_ci');
            $table->string('Nombre', 100)->nullable()->collation('utf8mb4_unicode_ci');
            $table->integer('Porcentaje')->nullable();
            $table->timestamps();
        });
    }
};
