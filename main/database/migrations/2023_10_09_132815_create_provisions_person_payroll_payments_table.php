<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProvisionsPersonPayrollPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('provisions_person_payroll_payments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('person_id');
            $table->unsignedInteger('payroll_payment_id');
            $table->integer('severance');
            $table->integer('severance_interest');
            $table->integer('prima');
            $table->integer('vacations');
            $table->double('accumulated_vacations', 5, 3);
            $table->integer('total_provisions');
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
        Schema::dropIfExists('provisions_person_payroll_payments');
    }
}
