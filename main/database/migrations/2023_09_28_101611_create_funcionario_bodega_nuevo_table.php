<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFuncionarioBodegaNuevoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Funcionario_Bodega_Nuevo', function (Blueprint $table) {
            $table->bigIncrements('Id_Funcionario_Bodega_Nuevo');
            $table->bigInteger('Id_Bodega_Nuevo');
            $table->bigInteger('Identificacion_Funcionario');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Funcionario_Bodega_Nuevo');
    }
}
