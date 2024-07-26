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
        Schema::create('variable_rule', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('variable_id');
            $table->unsignedBigInteger('rule_id');
            $table->foreign('variable_id')->references('id')->on('variables');
            $table->foreign('rule_id')->references('id')->on('rules');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('variable_rule');
    }
};
