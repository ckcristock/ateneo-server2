<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMarcationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('marcations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type', 191);
            $table->string('description', 191);
            $table->string('date', 191);
            $table->timestamps();
            $table->string('img', 250)->nullable();
            $table->integer('person_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('marcations');
    }
}
