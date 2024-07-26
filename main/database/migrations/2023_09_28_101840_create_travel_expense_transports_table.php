<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTravelExpenseTransportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('travel_expense_transports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('type', ['ida', 'vuelta']);
            $table->string('journey', 191);
            $table->string('company', 191);
            $table->enum('ticket_payment', ['agencia', 'viajero']);
            $table->date('departure_date');
            $table->integer('ticket_value');
            $table->integer('travel_expense_id');
            $table->double('total', 50, 2)->nullable();
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
        Schema::dropIfExists('travel_expense_transports');
    }
}
