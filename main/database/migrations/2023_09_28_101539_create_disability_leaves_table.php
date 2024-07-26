<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDisabilityLeavesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('disability_leaves', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('concept', 191);
            $table->string('accounting_account', 191);
            $table->boolean('sum')->default(0);
            $table->boolean('state')->default(1);
            $table->string('novelty', 191)->nullable();
            $table->string('modality', 191)->nullable();
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
        Schema::dropIfExists('disability_leaves');
    }
}
