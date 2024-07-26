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
        Schema::table('formality_type_service', function (Blueprint $table) {
            $table->unsignedBigInteger('formality_id')->nullable(false)->change();
            $table->unsignedBigInteger('type_service_id')->nullable(false)->change();
            $table->foreign('formality_id')->references('id')->on('formalities');
            $table->foreign('type_service_id')->references('id')->on('type_services');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('formality_type_service', function (Blueprint $table) {
            //
        });
    }
};
