<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCupLaboratoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cup_laboratories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('id_laboratory');
            $table->bigInteger('id_cup');
            $table->string('file', 100)->nullable();
            $table->enum('state', ['pendiente', 'subido'])->default('Pendiente');
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
        Schema::dropIfExists('cup_laboratories');
    }
}
