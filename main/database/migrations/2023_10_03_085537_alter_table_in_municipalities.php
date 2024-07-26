<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableInMunicipalities extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('municipalities', function (Blueprint $table) {
            $table->dropColumn('codigo_dane');
            $table->string('abbreviation', 10)->nullable();
            $table->integer('dian_code')->nullable();
            $table->integer('dane_code')->nullable();
            //$table->double('percentage_product', 50, 7);
            //$table->double('percentage_service', 50, 7);
            $table->enum('state', ['Activo', 'Inactivo'])->default('Activo');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('municipalities', function (Blueprint $table) {
            $table->integer('codigo_dane')->nullable();
            $table->dropColumn('abbreviation');
            $table->dropColumn('dian_code');
            $table->dropColumn('dane_code');
            $table->dropColumn('state');
        });
    }
}
