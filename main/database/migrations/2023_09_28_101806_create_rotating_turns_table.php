<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRotatingTurnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rotating_turns', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 191);
            $table->boolean('extra_hours')->default(0);
            $table->integer('entry_tolerance')->default(0);
            $table->integer('leave_tolerance')->default(0);
            $table->enum('state', ['activo', 'inactivo'])->default('Activo');
            $table->boolean('launch')->default(0);
            $table->boolean('breack')->default(0);
            $table->time('entry_time')->default('00:00:00');
            $table->time('leave_time')->default('00:00:00');
            $table->time('launch_time')->default('00:00:00');
            $table->time('launch_time_two')->default('00:00:00');
            $table->time('breack_time')->default('00:00:00');
            $table->time('breack_time_two')->default('00:00:00');
            $table->integer('sunday_id')->nullable();
            $table->integer('saturday_id')->nullable();
            $table->string('color', 191);
            $table->unsignedBigInteger('company_id')->default(0)->index('company_foreign');
            $table->timestamps();
            $table->unsignedInteger('empresa_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rotating_turns');
    }
}
