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
        Schema::table('Nacionalizacion_Parcial', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id');
        });
        Schema::table('Ajuste_Individual', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
