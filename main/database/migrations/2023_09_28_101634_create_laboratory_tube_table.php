<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLaboratoryTubeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('laboratory_tube', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('laboratory_id');
            $table->bigInteger('color_id');
            $table->bigInteger('amount');
            $table->time('hour')->nullable();
            $table->integer('count')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('laboratory_tube');
    }
}
