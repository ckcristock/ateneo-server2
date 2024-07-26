<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToInventarioContratoTable extends Migration
{
    public function up()
    {
        Schema::table('Inventario_Contrato', function (Blueprint $table) {
            
            $table->unsignedBigInteger('Id_Contrato')->change();
            $table->foreign('Id_Contrato')->references('Id_Contrato')->on('Contrato');

            $table->unsignedBigInteger('Id_Inventario_Nuevo')->change();
            $table->foreign('Id_Inventario_Nuevo')->references('Id_Inventario_Nuevo')->on('Inventario_Nuevo');

            $table->unsignedBigInteger('Id_Producto_Contrato')->change();
            $table->foreign('Id_Producto_Contrato')->references('Id_Producto_Contrato')->on('Producto_Contrato');
        });
    }

    public function down()
    {
        Schema::table('Inventario_Contrato', function (Blueprint $table) {
            $table->dropForeign(['Id_Contrato']);
            $table->dropForeign(['Id_Inventario_Nuevo']);
            $table->dropForeign(['Id_Producto_Contrato']);
        });
    }
}
