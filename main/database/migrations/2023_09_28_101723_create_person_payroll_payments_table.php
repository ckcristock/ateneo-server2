<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePersonPayrollPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('person_payroll_payments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('person_id');
            $table->unsignedInteger('payroll_payment_id');
            $table->integer('worked_days');
            $table->integer('salary');
            $table->integer('transportation_assistance')->default(0);
            $table->integer('retentions_deductions');
            $table->integer('net_salary');
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
        Schema::dropIfExists('person_payroll_payments');
    }
}
