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
        Schema::create('attention_call_memorandum', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attention_call_id');
            $table->unsignedBigInteger('memorandum_id');
            $table->foreign('attention_call_id')->references('id')->on('attention_calls')->onDelete('cascade');
            $table->foreign('memorandum_id')->references('id')->on('memorandums')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attention_call_memorandum');
    }
};
