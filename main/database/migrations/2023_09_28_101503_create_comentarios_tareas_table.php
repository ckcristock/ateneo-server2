<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateComentariosTareasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('comentarios_tareas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('id_person');
            $table->dateTime('fecha');
            $table->string('comentario', 1000);
            $table->integer('id_task');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('comentarios_tareas');
    }
}
