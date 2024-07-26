<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCiiuCodesTwoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('ciiu_codes');
        Schema::create('ciiu_codes', function (Blueprint $table) {
            $table->string('title', 300)->nullable();
            $table->string('subtitle', 300)->nullable();
            $table->integer('value')->nullable();
            $table->string('text', 300)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ciiu_codes_two');
    }
}
