<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLayoffsCertificatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('layoffs_certificates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('reason_withdrawal')->nullable();
            $table->bigInteger('person_id')->nullable();
            $table->string('reason', 500)->nullable();
            $table->string('monto', 50)->nullable();
            $table->bigInteger('valormonto')->nullable();
            $table->string('document', 100)->nullable();
            $table->enum('state', ['Pendiente', 'Aprobada', 'Rechazada'])->nullable()->default('Pendiente');
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
        Schema::dropIfExists('layoffs_certificates');
    }
}
