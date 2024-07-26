<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistoryDataCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('history_data_companies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('namespace');
            $table->string('data_name');
            $table->dateTime('date_end');
            $table->string('value')->nullable();
            $table->integer('person_id');
            $table->unsignedBigInteger('company_id');
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
        Schema::dropIfExists('history_data_companies');
    }
}
