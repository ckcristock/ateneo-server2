<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActividadOrdenCompraTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Actividad_Orden_Compra', function (Blueprint $table) {
            $table->bigIncrements('Id_Actividad_Orden_Compra');
            $table->integer('Id_Orden_Compra_Nacional')->nullable();
            $table->bigInteger('Id_Acta_Recepcion_Compra')->nullable();
            $table->bigInteger('Identificacion_Funcionario')->nullable();
            $table->dateTime('Fecha')->nullable();
            $table->text('Detalles')->nullable();
            $table->string('Estado', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Actividad_Orden_Compra');
    }
}
