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
        Schema::create('disciplinary_process_actions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('disciplinary_process_id');
            $table->unsignedBigInteger('user_id');
            $table->longText('description');
            $table->string('file', 500)->nullable();
            $table->timestamps();
            $table->foreign('disciplinary_process_id')->references('id')->on('disciplinary_processes');
            $table->foreign('user_id')->references('id')->on('usuario');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disciplinary_process_actions');
    }
};
