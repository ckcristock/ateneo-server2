<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddActiveColumnSeveranceFundsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('severance_funds', function (Blueprint $table) {

            $table->enum('status', ['Activo', 'Inactivo'])->default('Activo');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('severance_funds', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
}
