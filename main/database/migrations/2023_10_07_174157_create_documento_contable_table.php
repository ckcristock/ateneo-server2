<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class CreateDocumentoContableTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Documento_Contable', function (Blueprint $table) {
            $table->bigIncrements('Id_Documento_Contable');
            $table->date('Fecha_Documento')->nullable();
            $table->integer('Id_Empresa')->nullable();
            $table->string('Codigo', 45)->nullable();
            $table->bigInteger('Beneficiario')->nullable()->default(804016084);
            $table->string('Tipo_Beneficiario', 45)->nullable();
            $table->text('Concepto')->nullable();
            $table->text('Notas_Recibo')->nullable();
            $table->integer('Identificacion_Funcionario')->nullable();
            $table->string('Estado', 45)->nullable()->default('Activo');
            $table->string('Codigo_Qr', 250)->nullable();
            $table->integer('Id_Centro_Costo')->nullable();
            $table->string('Documento', 250)->nullable();
            $table->string('Egreso', 45)->nullable()->default('No');
            $table->string('Forma_Pago', 45)->nullable();
            $table->string('Tipo', 45)->nullable()->default('Nota Contable');
            $table->timestamp('Fecha_Registro')->nullable()->useCurrent();
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
        Schema::dropIfExists('Documento_Contable');
    }
}
;
