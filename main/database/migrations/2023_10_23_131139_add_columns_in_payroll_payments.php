<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsInPayrollPayments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payroll_payments', function (Blueprint $table) {
            $table->string('code', 50)->nullable();
            $table->tinyInteger('electronic_reported')->nullable();
            $table->timestamp('electronic_reported_date')->nullable();
            $table->integer('user_electronic_reported')->nullable();
            $table->tinyInteger('email_reported')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payroll_payments', function (Blueprint $table) {
            //
        });
    }
}
