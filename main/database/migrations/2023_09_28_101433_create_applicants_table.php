<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApplicantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('applicants', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('job_id')->default(0);
            $table->string('name', 150)->nullable();
            $table->string('surname', 150)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('phone', 150)->nullable();
            $table->string('city', 150)->nullable();
            $table->string('passport', 150)->nullable();
            $table->tinyInteger('visa')->nullable();
            $table->integer('visaType_id')->nullable();
            $table->string('education')->nullable();
            $table->integer('experience_year')->nullable();
            $table->string('conveyance', 50)->nullable();
            $table->integer('driving_license_id')->nullable();
            $table->string('curriculum', 500)->nullable();
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
        Schema::dropIfExists('applicants');
    }
}
