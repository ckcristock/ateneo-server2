<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventarioFisicoNuevoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Inventario_Fisico_Nuevo', function (Blueprint $table) {
            $table->bigIncrements('Id_Inventario_Fisico_Nuevo');
            $table->integer('Funcionario_Autoriza');
            $table->integer('Id_Bodega_Nuevo');
            $table->integer('Id_Grupo_Estiba');
            $table->date('Fecha');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Inventario_Fisico_Nuevo');
    }
}
