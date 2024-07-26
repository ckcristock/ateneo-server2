<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class CreateTipoServicioTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Tipo_Servicio', function (Blueprint $table) {
            $table->bigIncrements('Id_Tipo_Servicio');
            $table->string('Codigo', 100)->nullable();
            $table->string('Nombre', 100)->nullable();
            $table->text('Nota');
            $table->integer('Id_Servicio')->nullable();
            $table->enum('Codigo_CIE', ['Si', 'No'])->nullable();
            $table->string('Tipo_Lista', 500)->nullable();
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
        Schema::dropIfExists('Tipo_Servicio');
    }
}
;
