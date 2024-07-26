<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayrollFactorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payroll_factors', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('person_id')->index('novedad_funcionario_id_foreign');
            $table->unsignedInteger('disability_leave_id')->index('novedad_contable_licencia_incapacidad_id_foreign');
            $table->dateTime('date_start');
            $table->dateTime('date_end');
            $table->dateTime('payback_date')->nullable();
            $table->string('disability_type', 191);
            $table->string('sum', 191);
            $table->string('modality', 191);
            $table->text('observation')->nullable();
            $table->timestamps();
            $table->integer('number_days')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payroll_factors');
    }
}
