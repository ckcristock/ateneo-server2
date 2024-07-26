<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHorarioTurnoRotativoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('horario_turno_rotativo', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('funcionario_id')->index('horario_turno_rotativo_funcionario_id_foreign');
            $table->unsignedInteger('turno_rotativo_id')->index('horario_turno_rotativo_turno_rotativo_id_foreign');
            $table->date('fecha');
            $table->integer('numero_semana');
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
        Schema::dropIfExists('horario_turno_rotativo');
    }
}
