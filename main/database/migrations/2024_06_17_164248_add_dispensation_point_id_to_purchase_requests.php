<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('dispensation_point_id')->nullable();
            $table->foreign('dispensation_point_id')->references('Id_Punto_Dispensacion')->on('Punto_Dispensacion');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->dropForeign('purchase_requests_dispensation_point_id_foreign');
            $table->dropColumn('dispensation_point_id');
        });
    }
};
