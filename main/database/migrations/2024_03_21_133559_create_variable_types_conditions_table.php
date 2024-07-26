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
        Schema::create('variable_types_conditions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('variable_type_id');
            $table->foreign('variable_type_id')->references('id')->on('variable_types');
            $table->unsignedBigInteger('type_condition_id');
            $table->foreign('type_condition_id')->references('id')->on('type_conditions');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('variable_types_conditions');
    }
};
