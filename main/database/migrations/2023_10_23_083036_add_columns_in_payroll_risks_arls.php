<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsInPayrollRisksArls extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payroll_risks_arls', function (Blueprint $table) {
            $table->unsignedBigInteger('account_plan_id')->nullable();
            $table->unsignedBigInteger('account_setoff')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payroll_risks_arls', function (Blueprint $table) {
            //
        });
    }
}
