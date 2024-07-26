<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterStructureInPayrollConfiguration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_payroll_manager', function (Blueprint $table) { //crear modelo
            $table->bigIncrements('id');
            $table->unsignedBigInteger('payroll_manager_id');
            $table->unsignedBigInteger('person_id');
            $table->unsignedBigInteger('company_id');
            $table->timestamps();
        });
        Schema::table('payroll_managers', function (Blueprint $table) {
            $table->dropColumn('manager');
        });
        Schema::create('company_countable_salary', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('countable_salary_id');
            $table->unsignedBigInteger('account_plan_id');
            $table->unsignedBigInteger('account_setoff');
            $table->unsignedBigInteger('company_id');
            $table->tinyInteger('status');
            $table->timestamps();
        });
        Schema::table('countable_salaries', function (Blueprint $table) {
            $table->dropColumn('account_plan_id');
            $table->dropColumn('account_setoff');
            $table->dropColumn('status');
        });
        Schema::create('company_payroll_overtime', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('payroll_overtime_id');
            $table->unsignedBigInteger('account_plan_id');
            $table->unsignedBigInteger('company_id');
            $table->decimal('percentage', 4, 2)->nullable();
            $table->timestamps();
        });
        Schema::table('payroll_overtimes', function (Blueprint $table) {
            $table->dropColumn('account_plan_id');
            $table->dropColumn('percentage');
        });
        Schema::create('company_payroll_social_security_person', function (BluePrint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('payroll_social_security_person_id');
            $table->unsignedBigInteger('account_plan_id');
            $table->unsignedBigInteger('account_setoff');
            $table->unsignedBigInteger('company_id');
            $table->decimal('percentage', 4, 2)->nullable();
            $table->timestamps();
        });
        Schema::table('payroll_social_security_people', function (BluePrint $table) {
            $table->dropColumn('account_plan_id');
            $table->dropColumn('account_setoff');
            $table->dropColumn('percentage');
        });
        Schema::create('company_payroll_social_security_company', function (BluePrint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('payroll_social_security_company_id');
            $table->unsignedBigInteger('account_plan_id');
            $table->unsignedBigInteger('account_setoff');
            $table->unsignedBigInteger('company_id');
            $table->decimal('percentage', 4, 2)->nullable();
            $table->timestamps();
        });
        Schema::table('payroll_social_security_companies', function (BluePrint $table) {
            $table->dropColumn('account_plan_id');
            $table->dropColumn('account_setoff');
            $table->dropColumn('percentage');
        });
        Schema::create('company_payroll_risks_arl', function (BluePrint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('payroll_risks_arl_id');
            $table->unsignedBigInteger('account_plan_id');
            $table->unsignedBigInteger('account_setoff');
            $table->unsignedBigInteger('company_id');
            $table->decimal('percentage', 4, 2)->nullable();
            $table->timestamps();
        });
        Schema::table('payroll_risks_arls', function (BluePrint $table) {
            $table->dropColumn('account_plan_id');
            $table->dropColumn('account_setoff');
            $table->dropColumn('percentage');
        });
        Schema::create('company_payroll_parafiscal', function (BluePrint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('payroll_parafiscal_id');
            $table->unsignedBigInteger('account_plan_id');
            $table->unsignedBigInteger('account_setoff');
            $table->unsignedBigInteger('company_id');
            $table->decimal('percentage', 4, 2)->nullable();
            $table->timestamps();
        });
        Schema::table('payroll_parafiscals', function (BluePrint $table) {
            $table->dropColumn('account_plan_id');
            $table->dropColumn('account_setoff');
            $table->dropColumn('percentage');
        });
        Schema::table('payroll_disability_leaves', function (BluePrint $table) { //este es como si fuera company_id
            $table->dropColumn('prefix');
            $table->dropColumn('concept');
            $table->unsignedBigInteger('disability_leave_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
        });
        Schema::table('countable_income', function (BluePrint $table) {
            $table->unsignedBigInteger('account_plan_id');
            $table->unsignedBigInteger('company_id');
            $table->dropColumn('accounting_account');
        });
        Schema::table('countable_deductions', function (BluePrint $table) {
            $table->unsignedBigInteger('account_plan_id');
            $table->unsignedBigInteger('company_id');
            $table->dropColumn('accounting_account');
        });
        Schema::create('company_countable_liquidation', function (BluePrint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('countable_liquidation_id');
            $table->unsignedBigInteger('account_plan_id');
            $table->unsignedBigInteger('company_id');
            $table->tinyInteger('status');
            $table->timestamps();
        });
        Schema::table('countable_liquidations', function (BluePrint $table) {
            $table->dropColumn('account_plan_id');
            $table->dropColumn('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payroll_configuration', function (Blueprint $table) {
            //
        });
    }
}
