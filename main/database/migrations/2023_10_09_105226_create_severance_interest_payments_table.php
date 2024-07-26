<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSeveranceInterestPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('severance_interest_payments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('year')->nullable();
            $table->bigInteger('total')->nullable();
            $table->integer('total_employees')->nullable();
            $table->integer('user_id')->nullable();
            $table->string('type', 50)->default('Pago de intereses');
            $table->unsignedInteger('company_id')->default(1);
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
        Schema::dropIfExists('severance_interest_payments');
    }
}
