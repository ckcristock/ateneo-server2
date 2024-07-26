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
        Schema::create('VariableConditionsValues', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('variable_id');
            $table->unsignedBigInteger('type_condition_id');
            $table->string('value');
            $table->foreign('variable_id')->references('id')->on('variables');
            $table->foreign('type_condition_id')->references('id')->on('type_conditions');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('VariableConditionsValues');
    }
};
