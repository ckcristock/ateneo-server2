<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToInventarioNuevoTable extends Migration
{
    public function up()
    {
        Schema::table('Inventario_Nuevo', function (Blueprint $table) {

            $table->unsignedBigInteger('Id_Bodega')->nullable(false)->change();
            $table->foreign('Id_Bodega')->references('Id_Bodega_Nuevo')->on('Bodega_Nuevo');

            $table->unsignedBigInteger('Id_Estiba')->nullable(false)->change();
            $table->foreign('Id_Estiba')->references('Id_Estiba')->on('Estiba');

            $table->unsignedBigInteger('Id_Producto')->nullable(false)->change();
            $table->foreign('Id_Producto')->references('Id_Producto')->on('Producto');

            $table->unsignedBigInteger('Id_Punto_Dispensacion')->nullable(true)->change();
            $table->foreign('Id_Punto_Dispensacion')->references('Id_Punto_Dispensacion')->on('Punto_Dispensacion');

            $table->unsignedBigInteger('Identificacion_Funcionario')->nullable(false)->change();
            $table->foreign('Identificacion_Funcionario')->references('identifier')->on('people');
        });

        Schema::dropIfExists('Inventario_Viejo');
    }
    public function down()
    {
        Schema::table('Inventario_Nuevo', function (Blueprint $table) {
            $table->dropForeign(['Id_Bodega']);
            $table->dropForeign(['Id_Estiba']);
            $table->dropForeign(['Id_Producto']);
            $table->dropForeign(['Id_Punto_Dispensacion']);
            $table->dropForeign(['Identificacion_Funcionario']);
        });
    }
}
;
