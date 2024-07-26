<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayVacationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pay_vacations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('payroll_factor_id');
            $table->integer('days');
            $table->enum('state', ['pago', 'no pagado'])->default('No Pagado');
            $table->double('value')->default(0);
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
        Schema::dropIfExists('pay_vacations');
    }
}
