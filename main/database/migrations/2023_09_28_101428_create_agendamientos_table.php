<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAgendamientosTable extends Migration
{
    /*! REVISAR BASE DE DATOS EN CPANEL */
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agendamientos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('type_agenda_id')->nullable();
            $table->bigInteger('type_appointment_id')->nullable();
            $table->integer('ips_id')->nullable();
            $table->integer('eps_id')->nullable();
            $table->integer('location_id')->nullable();
            $table->integer('speciality_id')->nullable();
            $table->integer('person_id')->nullable();
            $table->integer('user_id')->nullable();
            $table->date('date_start')->nullable();
            $table->date('date_end')->nullable();
            $table->string('long', 5)->nullable();
            $table->time('hour_start')->nullable();
            $table->time('hour_end')->nullable();
            $table->json('days')->nullable();
            $table->boolean('pending')->default(0);
            $table->enum('state', ['aperturada', 'cancelada'])->default('Aperturada');
            $table->integer('share')->default(1);
            $table->bigInteger('regional_percent')->nullable();
            $table->integer('department_id')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('agendamientos');
    }
}
