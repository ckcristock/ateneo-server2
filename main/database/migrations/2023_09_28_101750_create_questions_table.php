<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuestionsTable extends Migration
{
    /*! REVISAR BASE DE DATOS EN CPANEL */
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('formulario_id')->nullable();
            $table->string('Pregunta')->default('');
            $table->string('Tipo', 50)->default('');
            $table->string('Valor', 50)->default('');
            $table->enum('Estado', ['activo', 'inactivo'])->default('Activo');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('questions');
    }
}
