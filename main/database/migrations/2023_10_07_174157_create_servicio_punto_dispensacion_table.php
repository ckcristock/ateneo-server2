<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;



class CreateServicioPuntoDispensacionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Servicio_Punto_Dispensacion', function (Blueprint $table) {
            $table->bigIncrements('Id_Servicio_Punto_Dispensacion');
            $table->integer('Id_Punto_Dispensacion');
            $table->integer('Id_Servicio');
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
        Schema::dropIfExists('Servicio_Punto_Dispensacion');
    }
}
;
