<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRotatingTurnDiariesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rotating_turn_diaries', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('person_id')->nullable()->index('diario_turno_rotativo_funcionario_id_foreign');
            $table->date('date')->nullable();
            $table->date('leave_date')->nullable();
            $table->unsignedInteger('rotating_turn_id')->index('diario_turno_rotativo_turno_rotativo_id_foreign');
            $table->time('entry_time_one')->nullable();
            $table->date('launch_one_date')->nullable();
            $table->date('launch_two_date')->nullable();
            $table->date('breack_one_date')->nullable();
            $table->date('breack_two_date')->nullable();
            $table->time('leave_time_one')->nullable();
            $table->time('launch_time_one')->nullable();
            $table->time('launch_time_two')->nullable();
            $table->time('breack_time_one')->nullable();
            $table->time('breack_time_two')->nullable();
            $table->string('img_one', 191)->nullable();
            $table->string('img_launch_one')->nullable();
            $table->string('img_launch_two')->nullable();
            $table->string('img_breack_one')->nullable();
            $table->string('img_breack_two')->nullable();
            $table->decimal('temp_one', 10, 1)->default(0.0);
            $table->decimal('temp_two', 10, 1)->default(0.0);
            $table->string('img_two', 191)->nullable();
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
        Schema::dropIfExists('rotating_turn_diaries');
    }
}
