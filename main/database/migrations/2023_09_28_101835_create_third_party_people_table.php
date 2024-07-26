<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateThirdPartyPeopleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('third_party_people', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 50)->nullable();
            $table->integer('n_document')->nullable();
            $table->integer('landline')->nullable();
            $table->integer('cell_phone')->nullable();
            $table->string('email', 50)->nullable();
            $table->string('usuario', 100)->nullable();
            $table->string('password', 100)->nullable();
            $table->string('position', 50)->nullable();
            $table->string('observation', 50)->nullable();
            $table->bigInteger('laboratory_id')->nullable();
            $table->integer('third_party_id')->nullable();
            $table->string('photo', 100)->nullable();
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
        Schema::dropIfExists('third_party_people');
    }
}
