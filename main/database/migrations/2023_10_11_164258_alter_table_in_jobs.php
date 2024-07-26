<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableInJobs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->unsignedBigInteger('driving_license');
            $table->integer('months_experience')->nullable()->after('experience_year');
            $table->enum('gener', ['No aplica', 'Masculino', 'Femenino'])->default('No aplica');
            $table->enum('languages', ['Inglés', 'Español'])->default('Español');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('jobs', function (Blueprint $table) {
            //
        });
    }
}
