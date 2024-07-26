<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class CreateParametroReporteResumenRetencionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Parametro_Reporte_Resumen_Retencion', function (Blueprint $table) {
            $table->bigIncrements('Id_Parametro_Reporte_Resumen_Retencion');
            $table->integer('Id_Plan_Cuenta')->nullable();
            $table->string('Concepto', 250)->nullable();
            $table->string('Tipo_Retencion', 45)->nullable();
            $table->enum('Tipo_Valor', ['D', 'C', 'D-C', 'C-D', 'Saldo'])->nullable();
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
        Schema::dropIfExists('Parametro_Reporte_Resumen_Retencion');
    }
}
;
