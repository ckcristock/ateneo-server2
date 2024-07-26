<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePreliquidatedLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('preliquidated_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('person_id');
            $table->string('person_identifier');
            $table->string('full_name');
            $table->dateTime('liquidated_at');
            $table->unsignedBigInteger('person_work_contract_id');
            $table->unsignedBigInteger('reponsible_id');
            $table->string('responsible_identifier', 50)->nullable();
            $table->enum('status', ['Liquidado', 'Reincorporado', 'PreLiquidado'])->nullable();
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
        Schema::dropIfExists('preliquidated_logs');
    }
}
