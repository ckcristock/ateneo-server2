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
        Schema::create('conditions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('variable_id');
            $table->unsignedBigInteger('operator_id');
            $table->foreign('variable_id')->references('id')->on('variables');
            $table->foreign('operator_id')->references('id')->on('operators');
            $table->string('value');
            $table->enum('logical_operator', ['and', 'or']);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conditions');
    }
};
