<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoanFeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loan_fees', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('loan_id')->nullable();
            $table->integer('number')->nullable();
            $table->decimal('amortization', 50, 2)->nullable();
            $table->decimal('interest', 50, 2)->nullable();
            $table->decimal('value', 50, 2)->nullable();
            $table->decimal('outstanding_balance', 50, 2)->nullable();
            $table->date('date')->nullable();
            $table->timestamp('payment_date')->nullable();
            $table->enum('state', ['pendiente', 'paga'])->default('Pendiente');
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
        Schema::dropIfExists('loan_fees');
    }
}
