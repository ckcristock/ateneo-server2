<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class CreateTipoServicioPuntoDispensacionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Tipo_Servicio_Punto_Dispensacion', function (Blueprint $table) {
            $table->bigIncrements('Id_Tipo_Servicio_Punto_Dispensacion');
            $table->integer('Id_Tipo_Servicio');
            $table->integer('Id_Punto_Dispensacion');
            $table->timestamp('Fecha_Registro')->useCurrent();
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
        Schema::dropIfExists('Tipo_Servicio_Punto_Dispensacion');
    }
}
;
