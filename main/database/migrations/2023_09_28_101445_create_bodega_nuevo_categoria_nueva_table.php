<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBodegaNuevoCategoriaNuevaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Bodega_Nuevo_Categoria_Nueva', function (Blueprint $table) {
            $table->bigIncrements('Id_Bodega_Nuevo_Categoria_Nueva');
            $table->integer('Id_Bodega_Nuevo');
            $table->bigInteger('Id_Categoria_Nueva');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Bodega_Nuevo_Categoria_Nueva');
    }
}
