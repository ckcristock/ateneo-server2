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
        Schema::table('Categoria_Nueva', function (Blueprint $table) {
            $table->json('general_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('Categoria_Nueva', function (Blueprint $table) {
            $table->dropColumn('general_name');
        });
    }
};
