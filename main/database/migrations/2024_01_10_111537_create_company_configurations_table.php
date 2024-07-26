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
        Schema::create('company_configurations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->unique()->constrained();
            $table->unsignedInteger('max_memos_per_employee')->default(3);
            $table->unsignedInteger('attention_expiry_days')->default(60);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_configurations');
    }
};
