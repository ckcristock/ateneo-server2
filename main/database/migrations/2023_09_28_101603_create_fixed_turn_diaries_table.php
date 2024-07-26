<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFixedTurnDiariesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fixed_turn_diaries', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('person_id')->index('diario_turno_fijo_funcionario_id_foreign');
            $table->date('date');
            $table->unsignedInteger('fixed_turn_id')->index('diario_turno_fijo_turno_fijo_id_foreign');
            $table->time('entry_time_one')->nullable();
            $table->time('leave_time_one')->nullable();
            $table->time('entry_time_two')->nullable();
            $table->time('leave_time_two')->nullable();
            $table->string('img_one', 191)->nullable();
            $table->string('img_two', 191)->nullable();
            $table->string('img_three', 191)->nullable();
            $table->string('img_four', 191)->nullable();
            $table->string('latitud', 191)->nullable();
            $table->string('longitud', 191)->nullable();
            $table->string('latitud_dos', 191)->nullable();
            $table->string('longitud_dos', 191)->nullable();
            $table->string('latitud_tres', 191)->nullable();
            $table->string('longitud_tres', 191)->nullable();
            $table->string('latitud_cuatro', 191)->nullable();
            $table->string('longitud_cuatro', 191)->nullable();
            $table->timestamps();
            $table->decimal('temp_one', 11, 1)->default(0.0);
            $table->decimal('temp_two', 11, 1)->default(0.0);
            $table->decimal('temp_three', 11, 1)->default(0.0);
            $table->decimal('temp_four', 11, 1)->default(0.0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fixed_turn_diaries');
    }
}
