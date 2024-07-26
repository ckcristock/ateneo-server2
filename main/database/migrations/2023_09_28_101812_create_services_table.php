<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('services', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('technic_note_id')->nullable();
            $table->string('frequency', 5)->nullable();
            $table->bigInteger('centro_costo_id')->nullable();
            $table->bigInteger('cup_id')->nullable();
            $table->string('code', 50)->nullable();
            $table->string('value', 50)->nullable();
            $table->integer('speciality_id')->nullable();
            $table->integer('route_id')->nullable();
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
        Schema::dropIfExists('services');
    }
}
