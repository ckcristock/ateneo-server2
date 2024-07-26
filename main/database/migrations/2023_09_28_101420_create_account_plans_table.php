<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_plans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type_p', 191)->nullable();
            $table->string('type_niif', 191)->nullable();
            $table->integer('code')->nullable();
            $table->string('name', 191);
            $table->string('niif_code', 191);
            $table->string('niif_name', 191);
            $table->string('status', 50)->nullable();
            $table->string('accounting_adjustment', 50)->nullable();
            $table->string('close_third', 50)->nullable();
            $table->string('movement', 50)->nullable();
            $table->string('document', 50)->nullable();
            $table->string('base', 50)->nullable();
            $table->integer('value')->nullable();
            $table->decimal('percent', 20, 2)->nullable();
            $table->string('center_cost', 50)->nullable();
            $table->string('depreciation', 50)->nullable();
            $table->string('exogenous', 50)->nullable();
            $table->string('nature', 191)->nullable();
            $table->integer('close_nit')->nullable();
            $table->string('bank', 191)->nullable();
            $table->integer('bank_id')->nullable();
            $table->string('nit', 191)->nullable();
            $table->string('annual_voucher', 50)->nullable();
            $table->string('report', 191)->nullable();
            $table->enum('class_account', ['ahorros', 'corriente'])->nullable();
            $table->string('niif', 191)->nullable();
            $table->string('account_number', 50)->nullable();
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
        Schema::dropIfExists('account_plans');
    }
}
