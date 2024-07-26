<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTravelExpenseFeedingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('travel_expense_feedings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('type', ['nacional', 'internacional']);
            $table->enum('breakfast', ['si', 'no']);
            $table->integer('rate')->nullable();
            $table->integer('travel_expense_id')->nullable();
            $table->integer('stay')->nullable();
            $table->double('total', 50, 2)->nullable();
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
        Schema::dropIfExists('travel_expense_feedings');
    }
}
