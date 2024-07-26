<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActividadProductosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Actividad_Producto', function (Blueprint $table) {
            $table->bigIncrements('Id_Actividad_Producto');
            $table->bigInteger('Id_Producto')->nullable();
            $table->integer('Person_Id')->nullable();
            $table->text('Detalles')->nullable();
            $table->timestamp('Fecha')->useCurrentOnUpdate()->useCurrent();
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
        Schema::dropIfExists('Actividad_Producto');
    }
}
