<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePremiumPeopleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('premium_people', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('premium_id')->nullable();
            $table->integer('person_id')->nullable();
            $table->integer('digit_person')->nullable();
            $table->string('details', 500)->nullable();
            $table->integer('worked_days')->nullable();
            $table->integer('salary')->nullable();
            $table->integer('total_premium')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('premium_people');
    }
}
