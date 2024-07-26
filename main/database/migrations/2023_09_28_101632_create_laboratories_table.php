<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLaboratoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('laboratories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('patient')->default(0);
            $table->dateTime('date');
            $table->bigInteger('contract_id');
            $table->bigInteger('professional_id');
            $table->bigInteger('cie10_id');
            $table->bigInteger('motivo_id')->nullable();
            $table->string('observations', 500)->nullable();
            $table->bigInteger('laboratory_id')->nullable();
            $table->bigInteger('ips_id')->nullable();
            $table->bigInteger('specialty_id')->nullable();
            $table->time('hour')->nullable();
            $table->enum('status', ['ingresado', 'tomado', 'anulado'])->default('Ingresado');
            $table->enum('status_tube', ['true', 'false'])->default('false');
            $table->string('file_order', 100)->nullable();
            $table->string('file_document', 100)->nullable();
            $table->string('file_consentimiento', 100)->nullable();
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
        Schema::dropIfExists('laboratories');
    }
}
