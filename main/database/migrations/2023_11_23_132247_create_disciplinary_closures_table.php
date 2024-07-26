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
        Schema::create('disciplinary_closures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('disciplinary_closure_reasons_id');
            $table->unsignedBigInteger('disciplinary_process_id');
            $table->unsignedBigInteger('user_id');
            $table->longText('description');
            $table->string('file', 500);
            $table->foreign('disciplinary_closure_reasons_id')->references('id')->on('disciplinary_closure_reasons');
            $table->foreign('disciplinary_process_id')->references('id')->on('disciplinary_processes');
            $table->foreign('user_id')->references('id')->on('usuario');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disciplinary_closures');
    }
};
