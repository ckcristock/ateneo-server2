<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToAjusteIndividualTable extends Migration
{
    public function up()
    {
        Schema::table('Ajuste_Individual', function (Blueprint $table) {

            $table->unsignedBigInteger('Id_Clase_Ajuste_Individual')->change();
            $table->foreign('Id_Clase_Ajuste_Individual')->references('Id_Clase_Ajuste_Individual')->on('Clase_Ajuste_Individual');

            $table->unsignedBigInteger('company_id')->change();
            $table->foreign('company_id')->references('id')->on('companies');

            $table->unsignedBigInteger('Identificacion_Funcionario')->change();
            $table->foreign('Identificacion_Funcionario')->references('identifier')->on('people');

            $table->unsignedBigInteger('Funcionario_Anula')->change();
            $table->foreign('Funcionario_Anula')->references('identifier')->on('people');

            $table->unsignedBigInteger('Funcionario_Autoriza_Salida')->change();
            $table->foreign('Funcionario_Autoriza_Salida')->references('identifier')->on('people');
        });
    }

    public function down()
    {
        Schema::table('Ajuste_Individual', function (Blueprint $table) {
            $table->dropForeign(['Id_Clase_Ajuste_Individual']);
            $table->dropForeign(['company_id']);
            $table->dropForeign(['Identificacion_Funcionario']);
            $table->dropForeign(['Funcionario_Anula']);
            $table->dropForeign(['Funcionario_Autoriza_Salida']);
        });
    }
}
