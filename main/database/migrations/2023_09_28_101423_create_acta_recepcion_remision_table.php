<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActaRecepcionRemisionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Acta_Recepcion_Remision', function (Blueprint $table) {
            $table->bigIncrements('Id_Acta_Recepcion_Remision');
            $table->string('Codigo', 100)->nullable();
            $table->bigInteger('Id_Punto_Dispensacion')->nullable();
            $table->bigInteger('Id_Bodega')->nullable();
            $table->bigInteger('Id_Bodega_Nuevo')->nullable();
            $table->bigInteger('Identificacion_Funcionario')->nullable();
            $table->text('Observaciones')->nullable();
            $table->bigInteger('Id_Remision')->nullable();
            $table->string('Tipo', 60)->default('Punto de Dispensacion');
            $table->timestamp('Fecha')->useCurrent();
            $table->string('Codigo_Qr', 100)->default('');
            $table->string('Estado', 45)->default('Aprobada');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Acta_Recepcion_Remision');
    }
}
