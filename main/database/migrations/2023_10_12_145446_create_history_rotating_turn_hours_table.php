<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistoryRotatingTurnHoursTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('history_rotating_turn_hours', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('rotating_turn_hour_id');
            $table->unsignedBigInteger('person_id');
            $table->integer('batch');
            $table->enum('action', ['create', 'edit'])->default('create');
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
        Schema::dropIfExists('history_rotating_turn_hours');
    }
}
