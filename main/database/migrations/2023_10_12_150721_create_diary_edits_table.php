<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDiaryEditsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('diary_edits', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('diariable_id');
            $table->string('diariable_type');
            $table->double('hours', 50, 2);
            $table->longText('justification');
            $table->unsignedBigInteger('person_id');
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
        Schema::dropIfExists('diary_edits');
    }
}
