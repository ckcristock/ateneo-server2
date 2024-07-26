<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsInPersonPayrollPayments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('person_payroll_payments', function (Blueprint $table) {
            $table->integer('user_electronic_reported')->nullable();
            $table->timestamp('electronic_reported_date')->nullable();
            $table->tinyInteger('electronic_reported')->nullable();
            $table->string('status', 50)->nullable();
            $table->string('code', 50)->nullable();
            $table->string('cune', 300)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('person_payroll_payments', function (Blueprint $table) {
            //
        });
    }
}
