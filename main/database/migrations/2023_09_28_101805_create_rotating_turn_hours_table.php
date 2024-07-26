<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRotatingTurnHoursTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rotating_turn_hours', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('person_id')->index('horario_turno_rotativo_funcionario_id_foreign');
            $table->unsignedInteger('rotating_turn_id')->index('horario_turno_rotativo_turno_rotativo_id_foreign');
            $table->date('date');
            $table->integer('weeks_number');
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
        Schema::dropIfExists('rotating_turn_hours');
    }
}
