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
        Schema::table('dotations', function (Blueprint $table) {
            $table->dropColumn('delivery_state');
        });
        Schema::table('dotations', function (Blueprint $table) {
            $table->enum('delivery_state', ['Aprobado', 'Rechazado', 'Pendiente'])->default('Pendiente');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dotations', function (Blueprint $table) {
            //
        });
    }
};
