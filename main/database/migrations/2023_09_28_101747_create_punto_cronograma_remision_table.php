<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePuntoCronogramaRemisionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Punto_Cronograma_Remision', function (Blueprint $table) {
            $table->bigIncrements('Id_Punto_Cronograma_Remision');
            $table->integer('Id_Punto');
            $table->integer('Id_Cronograma');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Punto_Cronograma_Remision');
    }
}
