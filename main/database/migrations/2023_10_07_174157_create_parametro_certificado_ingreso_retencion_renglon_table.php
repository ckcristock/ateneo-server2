<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class CreateParametroCertificadoIngresoRetencionRenglonTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Parametro_Certificado_Ingreso_Retencion_Renglon', function (Blueprint $table) {
            $table->bigIncrements('Id_Parametro_Certificado_Ingreso_Retencion_Renglon');
            $table->integer('Renglon')->nullable();
            $table->string('Tipo_Valor', 45)->nullable();
            $table->text('Cuentas')->nullable();
            $table->timestamp('Created_At')->nullable()->useCurrent();
            $table->timestamp('Updated_At')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Parametro_Certificado_Ingreso_Retencion_Renglon');
    }
}
;
