<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompanyPaymentConfigurationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_payment_configurations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('calculate_work_disability', 50)->nullable();
            $table->string('pay_deductions', 50)->nullable();
            $table->string('recurring_payment', 50)->nullable();
            $table->string('payment_transport_subsidy', 50)->nullable();
            $table->enum('affects_transportation_subsidy', ['si', 'no'])->nullable();
            $table->enum('pay_vacations', ['si', 'no'])->nullable();
            $table->integer('company_id')->nullable();
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
        Schema::dropIfExists('company_payment_configurations');
    }
}
