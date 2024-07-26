<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsInExtraHourReports extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('extra_hour_reports');
        Schema::create('extra_hour_reports', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('person_id')->index('reporte_extras_funcionario_id_foreign');
            $table->date('date');
            $table->integer('ht');
            $table->integer('hed');
            $table->integer('hen');
            $table->integer('heddf');
            $table->integer('hendf');
            $table->integer('hrndf');
            $table->integer('hrn');
            $table->integer('hrddf');
            $table->integer('hed_reales');
            $table->integer('hen_reales');
            $table->integer('hedfd_reales');
            $table->integer('hedfn_reales');
            $table->integer('rn_reales');
            $table->integer('rf_reales');
            $table->integer('rnf_reales');
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
        Schema::table('extra_hour_reports', function (Blueprint $table) {
            //
        });
    }
}
