<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSocialSecurityPersonPayrollPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('social_security_person_payroll_payments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('person_id');
            $table->unsignedInteger('payroll_payment_id');
            $table->integer('health');
            $table->integer('pension');
            $table->integer('risks');
            $table->integer('sena');
            $table->integer('icbf');
            $table->integer('compensation_founds');
            $table->integer('total_social_security');
            $table->integer('total_parafiscals');
            $table->integer('total_social_security_parafiscals');
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
        Schema::dropIfExists('social_security_person_payroll_payments');
    }
}
