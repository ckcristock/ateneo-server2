<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHotelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hotels', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('type', ['nacional', 'internacional'])->nullable();
            $table->string('name', 191)->nullable();
            $table->string('city_id', 191)->nullable();
            $table->string('person_contact', 191)->nullable();
            $table->string('landline', 191)->nullable();
            $table->string('address', 191)->nullable();
            $table->string('phone', 191)->nullable();
            $table->string('simple_rate', 191)->nullable();
            $table->string('double_rate', 191)->nullable();
            $table->enum('breakfast', ['si', 'no'])->nullable();
            $table->enum('accommodation', ['sencilla', 'doble'])->nullable();
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
        Schema::dropIfExists('hotels');
    }
}
