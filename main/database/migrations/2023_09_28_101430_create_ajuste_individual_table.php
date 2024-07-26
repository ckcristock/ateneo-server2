<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAjusteIndividualTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Ajuste_Individual', function (Blueprint $table) {
            $table->bigIncrements('Id_Ajuste_Individual');
            $table->string('Codigo', 60)->nullable();
            $table->timestamp('Fecha')->nullable()->useCurrent();
            $table->integer('Identificacion_Funcionario')->nullable();
            $table->string('Tipo', 100)->nullable();
            $table->integer('Id_Clase_Ajuste_Individual')->nullable();
            $table->string('Origen_Destino', 60)->nullable();
            $table->bigInteger('Id_Origen_Estiba')->nullable();
            $table->bigInteger('Id_Origen_Destino')->nullable();
            $table->string('Codigo_Qr')->nullable();
            $table->string('Estado', 45)->default('Activo');
            $table->text('Observacion_Anulacion')->nullable();
            $table->integer('Funcionario_Anula')->nullable();
            $table->timestamp('Fecha_Anulacion')->nullable();
            $table->string('Estado_Salida_Bodega', 60)->nullable()->comment("''Pendiente', 'Aprobado', 'Anulado''");
            $table->string('Estado_Entrada_Bodega', 60)->nullable()->comment("''Pendiente','Aprobada','Anulado','Acomodada''");
            $table->integer('Funcionario_Autoriza_Salida')->nullable()->comment("id funcionario");
            $table->dateTime('Fecha_Aprobacion_Salida')->nullable();
            $table->tinyInteger('Cambio_Estiba')->nullable();
            $table->bigInteger('Id_Salida')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Ajuste_Individual');
    }
}
