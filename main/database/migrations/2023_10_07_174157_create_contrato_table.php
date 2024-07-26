<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContratoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Contrato', function (Blueprint $table) {
            $table->bigIncrements('Id_Contrato');
            $table->string('Tipo_Contrato', 100)->nullable();
            $table->string('Nombre_Contrato', 100)->nullable();
            $table->integer('Id_Cliente')->nullable();
            $table->date('Fecha_Inicio')->nullable();
            $table->date('Fecha_Fin')->nullable();
            $table->bigInteger('Presupuesto')->nullable();
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
        Schema::dropIfExists('Contrato');
    }
}
;
