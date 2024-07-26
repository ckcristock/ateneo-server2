<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBodegaNuevoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Bodega_Nuevo', function (Blueprint $table) {
            $table->bigIncrements('Id_Bodega_Nuevo');
            $table->string('Nombre');
            $table->string('Direccion');
            $table->string('Telefono', 60);
            $table->string('Mapa')->nullable();
            $table->string('Compra_Internacional', 10)->nullable();
            $table->enum('Estado', ['activo', 'inactivo']);
            $table->string('Tipo', 50)->nullable();
            $table->integer('company_id')->nullable();
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
        Schema::dropIfExists('Bodega_Nuevo');
    }
}
