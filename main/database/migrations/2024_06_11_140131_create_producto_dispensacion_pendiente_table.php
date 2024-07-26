<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('Producto_Dispensacion_Pendiente', function (Blueprint $table) {
            $table->bigIncrements('Id_Producto_Dispensacion_Pendiente');
            $table->unsignedBigInteger('Id_Producto_Dispensacion')->nullable();
            $table->integer('Cantidad_Entregada')->nullable();
            $table->integer('Cantidad_Pendiente')->nullable();
            $table->integer('Entregar_Faltante')->nullable();
            $table->timestamp('Timestamp')->useCurrent()->nullable();
            $table->index('Id_Producto_Dispensacion');
            $table->foreign('Id_Producto_Dispensacion')->references('Id_Producto_Dispensacion')->on('Producto_Dispensacion');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Producto_Dispensacion_Pendiente');
    }
};
