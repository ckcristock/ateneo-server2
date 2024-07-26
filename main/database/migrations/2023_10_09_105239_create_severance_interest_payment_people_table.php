<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSeveranceInterestPaymentPeopleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('severance_interest_payment_people', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('severance_interest_payment_id');
            $table->unsignedInteger('person_id');
            $table->bigInteger('total');
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
        Schema::dropIfExists('severance_interest_payment_people');
    }
}
