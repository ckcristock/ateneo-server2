<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class CreateContratoFuncionarioTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Contrato_Funcionario', function (Blueprint $table) {
            $table->bigIncrements('Id_Contrato_Funcionario');
            $table->bigInteger('Identificacion_Funcionario')->nullable()->index('Identificacion_Funcionario');
            $table->integer('Id_Tipo_Contrato')->nullable();
            $table->integer('Id_Salario')->nullable();
            $table->date('Fecha_Inicio_Contrato')->nullable();
            $table->date('Fecha_Fin_Contrato')->nullable();
            $table->integer('Id_Riesgo')->nullable();
            $table->decimal('Valor', 20)->nullable();
            $table->string('Estado', 1000)->default('Activo');
            $table->integer('Id_Municipio')->nullable();
            $table->decimal('Auxilio_No_Prestacional', 20)->default(0);
            $table->enum('Aporte_Pension', ['Si', 'No'])->default('Si');
            $table->integer('Numero_Otrosi')->default(1);
            $table->timestamp('Fecha_Preliquidado')->nullable();
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
        Schema::dropIfExists('Contrato_Funcionario');
    }
}
;
