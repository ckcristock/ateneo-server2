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
        Schema::table('category_variables', function (Blueprint $table) {
            $table->boolean('lists')->default(false)->after('reception');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('category_variables', function (Blueprint $table) {
            $table->dropColumn('lists');
        });
    }
};
