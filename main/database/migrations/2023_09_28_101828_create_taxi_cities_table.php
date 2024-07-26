<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaxiCitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('taxi_cities', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('type', ['nacional', 'internacional'])->nullable();
            $table->bigInteger('taxi_id');
            $table->bigInteger('city_id');
            $table->double('value', 50, 2);
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
        Schema::dropIfExists('taxi_cities');
    }
}
