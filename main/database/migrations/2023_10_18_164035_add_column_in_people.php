<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnInPeople extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('people', function (Blueprint $table) {
            $table->text('place_of_birth')->nullable();
            $table->enum('gener', ['Femenino', 'Masculino'])->nullable();
            $table->enum('visa', ['Si', 'No'])->nullable()->default('No');
            $table->string('passport_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('people', function (Blueprint $table) {
            $table->dropColumn('place_of_birth');
            $table->dropColumn('gener');
            $table->dropColumn('visa');
            $table->dropColumn('passport_number');
        });
    }
}
