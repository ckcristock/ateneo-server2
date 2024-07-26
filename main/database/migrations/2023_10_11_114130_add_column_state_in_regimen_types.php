<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnStateInRegimenTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('regimen_types', function (Blueprint $table) {
            $table->enum('state', ['Activo', 'Inactivo'])->default('Activo')->after('code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('regimen_types', function (Blueprint $table) {
            $table->dropColumn('state');
        });
    }
}
