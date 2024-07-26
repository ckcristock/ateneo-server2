<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class CreateCierreContableTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Cierre_Contable', function (Blueprint $table) {
            $table->bigIncrements('Id_Cierre_Contable');
            $table->string('Codigo', 45)->nullable();
            $table->bigInteger('Identificacion_Funcionario')->nullable();
            $table->string('Mes', 45)->nullable();
            $table->string('Anio', 45)->nullable();
            $table->text('Observaciones')->nullable();
            $table->enum('Tipo_Cierre', ['Mes', 'Anio'])->nullable()->default('Mes');
            $table->enum('Estado', ['Abierto', 'Cerrado', 'Anulado'])->nullable()->default('Cerrado');
            $table->integer('Id_Empresa')->nullable();
            $table->timestamp('Created_At')->nullable()->useCurrent();
            $table->timestamp('Updated_At')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Cierre_Contable');
    }
}
;
