<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class CreateDepreciacionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Depreciacion', function (Blueprint $table) {
            $table->bigIncrements('Id_Depreciacion');
            $table->string('Codigo', 45)->nullable();
            $table->dateTime('Fecha_Registro')->nullable()->useCurrent();
            $table->integer('Mes')->comment('Mes representado en forma numerica');
            $table->integer('Anio');
            $table->integer('Identificacion_Funcionario')->nullable();
            $table->string('Tipo', 45)->nullable()->default('PCGA');
            $table->string('Estado', 45)->nullable()->default('Activo');
            $table->bigInteger('Funcionario_Anula')->nullable();
            $table->dateTime('Fecha_Anulacion')->nullable();
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
        Schema::dropIfExists('Depreciacion');
    }
}
;
