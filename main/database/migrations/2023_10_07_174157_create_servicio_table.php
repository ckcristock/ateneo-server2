<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class CreateServicioTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Servicio', function (Blueprint $table) {
            $table->bigIncrements('Id_Servicio');
            $table->string('Nombre', 500)->nullable();
            $table->enum('Estado', ['Activo', 'Inactivo'])->default('Activo');
            $table->bigInteger('Identificacion_Funcionario')->nullable();
            $table->dateTime('Fecha')->nullable();
            $table->bigInteger('Funcionario_Inactiva')->nullable();
            $table->dateTime('Fecha_Inactivacion')->nullable();
            $table->integer('Cantidad_Formulada')->default(200);
            $table->enum('Autorizacion', ['Si', 'No'])->nullable()->default('Si');
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
        Schema::dropIfExists('Servicio');
    }
}
;
