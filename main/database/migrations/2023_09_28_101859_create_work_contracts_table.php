<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkContractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('work_contracts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('position_id')->nullable()->index('work_contracts_position_id_index');
            $table->integer('company_id')->nullable()->index('work_contracts_company_id_index');
            $table->boolean('liquidated')->default(0)->index('work_contracts_liquidated_index');
            $table->integer('person_id')->nullable()->index('work_contracts_person_id_index');
            $table->double('salary', 50, 2)->nullable();
            $table->enum('turn_type', ['rotativo', 'fijo'])->nullable()->index('work_contracts_turn_type_index');
            $table->integer('fixed_turn_id')->nullable();
            $table->date('date_of_admission')->nullable();
            $table->integer('work_contract_type_id')->nullable()->index('work_contracts_work_contract_type_id_index');
            $table->date('date_end')->nullable()->index('work_contracts_date_end_index');
            $table->timestamps();
            $table->integer('rotating_turn_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('work_contracts');
    }
}
