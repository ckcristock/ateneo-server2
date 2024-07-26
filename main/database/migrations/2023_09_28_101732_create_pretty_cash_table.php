<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePrettyCashTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pretty_cash', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('person_id')->default(0);
            $table->integer('account_plan_id')->nullable();
            $table->double('initial_balance', 50, 2)->nullable();
            $table->string('description', 50)->nullable();
            $table->integer('user_id')->nullable();
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
        Schema::dropIfExists('pretty_cash');
    }
}
