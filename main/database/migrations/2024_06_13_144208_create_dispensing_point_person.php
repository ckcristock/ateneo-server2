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
        Schema::create('dispensing_point_person', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dispensing_point_id');
            $table->unsignedBigInteger('person_id');
            $table->timestamps();

            $table->foreign('dispensing_point_id')->references('Id_Punto_Dispensacion')->on('Punto_Dispensacion')->onDelete('cascade');
            $table->foreign('person_id')->references('id')->on('people')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dispensing_point_person');
    }
};
