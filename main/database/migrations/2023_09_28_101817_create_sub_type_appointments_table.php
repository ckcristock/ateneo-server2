<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubTypeAppointmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sub_type_appointments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('type_appointment_id');
            $table->string('description');
            $table->string('name');
            $table->boolean('company_owner')->default(0);
            $table->boolean('procedure')->default(0);
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
        Schema::dropIfExists('sub_type_appointments');
    }
}
