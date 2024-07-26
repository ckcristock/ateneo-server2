<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApplicationCertificateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('application_certificate', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('person_id', 191);
            $table->string('patient_id', 191)->nullable();
            $table->string('cups_id', 191)->nullable();
            $table->date('date')->nullable();
            $table->string('diagnostic_id', 191)->nullable();
            $table->string('fileActa', 500)->nullable();
            $table->string('observation', 200)->nullable();
            $table->string('state', 20)->default('Activa');
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
        Schema::dropIfExists('application_certificate');
    }
}
