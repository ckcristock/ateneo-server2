<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMantisInTipoActivoFijo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Tipo_Activo_Fijo', function (Blueprint $table) {
            $table->string('Mantis', 45)->nullable()->default('No')->after('Consecutivo');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Tipo_Activo_Fijo', function (Blueprint $table) {
            //
        });
    }
}
