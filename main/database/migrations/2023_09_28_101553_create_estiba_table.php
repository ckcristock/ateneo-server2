<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEstibaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Estiba', function (Blueprint $table) {
            $table->bigIncrements('Id_Estiba');
            $table->string('Nombre');
            $table->bigInteger('Id_Grupo_Estiba');
            $table->bigInteger('Id_Bodega_Nuevo')->nullable();
            $table->integer('Id_Punto_Dispensacion')->nullable();
            $table->string('Codigo_Barras');
            $table->enum('Estado', ['disponible', 'inactiva', 'inventario'])->default('Disponible');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Estiba');
    }
}
