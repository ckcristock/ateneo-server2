<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTravelExpenseHotelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('travel_expense_hotels', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('travel_expense_id');
            $table->bigInteger('hotel_id');
            $table->integer('n_night');
            $table->string('who_cancels', 50);
            $table->string('accommodation', 50);
            $table->enum('breakfast', ['si', 'no']);
            $table->double('total', 50, 2)->nullable();
            $table->double('rate', 50, 2)->nullable();
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
        Schema::dropIfExists('travel_expense_hotels');
    }
}
