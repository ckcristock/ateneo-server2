<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('id_realizador');
            $table->string('tipo', 50);
            $table->string('titulo', 50);
            $table->string('descripcion', 250)->nullable();
            $table->date('fecha');
            $table->binary('adjuntos');
            $table->string('link', 50)->nullable();
            $table->integer('id_asignador');
            $table->time('hora')->nullable();
            $table->string('estado', 50);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tasks');
    }
}
