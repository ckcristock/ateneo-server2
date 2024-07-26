<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBonusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bonuses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('description');
            $table->double('total_bonuses');
            $table->integer('total_employees');
            $table->string('period', 50)->default('');
            $table->dateTime('payment_date');
            $table->enum('status', ['pendiente', 'pagado']);
            $table->string('payer');
            $table->string('payer_identifier');
            $table->string('payer_fullname')->comment('Pueden resultar reduntantes, pero útiles a nivel contable');
            $table->string('observations');
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
        Schema::dropIfExists('bonuses');
    }
}
