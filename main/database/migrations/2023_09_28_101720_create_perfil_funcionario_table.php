<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePerfilFuncionarioTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Perfil_Funcionario', function (Blueprint $table) {
            $table->bigIncrements('Id_Perfil_Funcionario');
            $table->integer('Id_Perfil')->nullable();
            $table->bigInteger('Identificacion_Funcionario')->nullable();
            $table->string('Titulo_Modulo', 100)->nullable();
            $table->string('Modulo', 100)->nullable();
            $table->string('Crear', 10)->nullable();
            $table->string('Editar', 10)->nullable();
            $table->string('Eliminar', 10)->nullable();
            $table->string('Ver', 10)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Perfil_Funcionario');
    }
}
