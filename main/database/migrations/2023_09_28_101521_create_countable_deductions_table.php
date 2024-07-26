<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCountableDeductionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('countable_deductions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('concept', 191);
            $table->string('accounting_account', 191);
            $table->enum('type', ['constitutivo', 'no constitutivo']);
            $table->boolean('state')->default(1);
            $table->boolean('editable')->default(1);
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
        Schema::dropIfExists('countable_deductions');
    }
}
