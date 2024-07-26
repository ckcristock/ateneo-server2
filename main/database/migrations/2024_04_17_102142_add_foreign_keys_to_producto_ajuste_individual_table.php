<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToProductoAjusteIndividualTable extends Migration
{
    public function up()
    {
        Schema::table('Producto_Ajuste_Individual', function (Blueprint $table) {

            $table->unsignedBigInteger('Id_Ajuste_Individual')->change();
            $table->foreign('Id_Ajuste_Individual')->references('Id_Ajuste_Individual')->on('Ajuste_Individual');

            $table->unsignedBigInteger('Id_Producto')->change();
            $table->foreign('Id_Producto')->references('Id_Producto')->on('Producto');

            $table->unsignedBigInteger('Id_Inventario')->change();
            $table->foreign('Id_Inventario')->references('Id_Inventario_Nuevo')->on('Inventario_Nuevo');

            $table->unsignedBigInteger('Id_Inventario_Nuevo')->change();
            $table->foreign('Id_Inventario_Nuevo')->references('Id_Inventario_Nuevo')->on('Inventario_Nuevo');

            $table->unsignedBigInteger('Id_Nueva_Estiba')->change();
            $table->foreign('Id_Nueva_Estiba')->references('Id_Estiba')->on('Estiba');

            $table->unsignedBigInteger('Id_Estiba_Acomodada')->change();
            $table->foreign('Id_Estiba_Acomodada')->references('Id_Estiba')->on('Estiba');
        });
    }

    public function down()
    {
        Schema::table('Producto_Ajuste_Individual', function (Blueprint $table) {
            $table->dropForeign(['Id_Ajuste_Individual']);
            $table->dropForeign(['Id_Producto']);
            $table->dropForeign(['Id_Inventario']);
            $table->dropForeign(['Id_Inventario_Nuevo']);
            $table->dropForeign(['Id_Nueva_Estiba']);
            $table->dropForeign(['Id_Estiba_Acomodada']);
        });
    }
}
