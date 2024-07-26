<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLaboratoriesPlacesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('laboratories_places', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 50);
            $table->bigInteger('nit');
            $table->integer('verification_digit');
            $table->string('city', 50);
            $table->string('contract', 50);
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
        Schema::dropIfExists('laboratories_places');
    }
}
