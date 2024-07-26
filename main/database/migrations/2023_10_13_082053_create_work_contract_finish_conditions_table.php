<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkContractFinishConditionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('work_contract_finish_conditions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->boolean('renewed')->unsigned()->default(true)->comment('true -> Renovar
false -> Preliquidar');
            $table->integer('contract_id')->nullable();
            $table->integer('work_contract_type_id')->nullable();
            $table->integer('contract_term_id')->nullable();
            $table->integer('group_id')->nullable();
            $table->integer('dependency_id')->nullable();
            $table->integer('position_id')->nullable();
            $table->integer('company_id')->nullable();
            $table->tinyInteger('liquidated')->nullable()->default(0);
            $table->integer('person_id')->nullable();
            $table->double('salary', 50, 2)->nullable();
            $table->enum('turn_type', ['Rotativo', 'Fijo'])->nullable();
            $table->integer('fixed_turn_id')->nullable();
            $table->integer('rotating_turn_id')->nullable();
            $table->date('date_of_admission')->nullable();
            $table->date('date_end')->nullable();
            $table->date('old_date_end')->nullable();
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
        Schema::dropIfExists('work_contract_finish_conditions');
    }
}
