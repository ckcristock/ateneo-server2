<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusCompensationFundsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('compensation_funds', function (Blueprint $table) {
            $table->dropColumn('status7');
        });
        Schema::table('compensation_funds', function (Blueprint $table) {
            $table->enum('status', ['Activo', 'Inactivo'])->default('activo');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('compensation_funds', function (Blueprint $table) {
            $table->dropColumn('status');
        });
        Schema::table('compensation_funds', function (Blueprint $table) {
            $table->enum('status7', ['activo', 'inactivo'])->default('Activo');
        });
    }
}
