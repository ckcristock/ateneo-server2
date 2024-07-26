<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsInPayrollSocialSecurityPeople extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payroll_social_security_people', function (Blueprint $table) {
            $table->string('account_plan_id', 191)->nullable();
            $table->string('account_setoff', 191)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payroll_social_security_people', function (Blueprint $table) {
            //
        });
    }
}
