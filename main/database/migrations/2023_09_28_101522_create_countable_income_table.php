<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCountableIncomeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('countable_income', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('concept', 50);
            $table->enum('type', ['constitutivo', 'no constitutivo']);
            $table->string('accounting_account', 191)->nullable();
            $table->boolean('state')->default(1);
            $table->boolean('editable')->default(0);
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
        Schema::dropIfExists('countable_income');
    }
}
