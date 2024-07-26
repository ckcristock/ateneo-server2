<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFixedTurnHoursTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fixed_turn_hours', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('day', 191);
            $table->unsignedInteger('fixed_turn_id')->index('horario_turno_fijo_turno_fijo_id_foreign');
            $table->string('entry_time_one', 191);
            $table->string('leave_time_one', 191);
            $table->enum('jornada_turno', ['diurno', 'nocturno'])->default('Diurno');
            $table->string('entry_time_two', 191)->nullable();
            $table->string('leave_time_two', 191)->nullable();
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
        Schema::dropIfExists('fixed_turn_hours');
    }
}
