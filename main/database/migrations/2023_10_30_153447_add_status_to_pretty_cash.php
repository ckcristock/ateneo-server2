<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusToPrettyCash extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pretty_cash', function (Blueprint $table) {
            $table->enum('status', ['Activa', 'Inactiva'])->default('Activa');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::table('pretty_cash', function (Blueprint $table) {
            $table->dropColumn('status');
        });

    }
}
