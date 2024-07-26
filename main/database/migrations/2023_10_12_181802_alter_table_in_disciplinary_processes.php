<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableInDisciplinaryProcesses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('disciplinary_processes', function (Blueprint $table) {
            $table->string('code', 500);
            $table->string('fileType', 20);
            $table->integer('approve_user_id')->default(0);
            $table->string('close_description', 500);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('disciplinary_processes', function (Blueprint $table) {
            //
        });
    }
}
