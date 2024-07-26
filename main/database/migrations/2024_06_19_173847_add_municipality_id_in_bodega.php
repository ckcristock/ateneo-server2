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
        Schema::table('Bodega_Nuevo', function (Blueprint $table) {
            $table->foreignId('municipality_id')->nullable()->constrained('municipalities');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bodega', function (Blueprint $table) {
            $table->dropColumn('municipality_id');
        });
    }
};
