<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayrollOvertimesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payroll_overtimes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('prefix', 191);
            $table->string('concept', 191);
            $table->decimal('percentage', 4, 2);
            $table->integer('account_plan_id')->nullable();
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
        Schema::dropIfExists('payroll_overtimes');
    }
}
