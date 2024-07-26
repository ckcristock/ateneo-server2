<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToBodegaNuevoGrupoEstibaEstibaTables extends Migration
{
    public function up()
    {
        
        Schema::table('Bodega_Nuevo', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->nullable(false)->change();
            $table->foreign('company_id')->references('id')->on('companies');
        });

        Schema::table('Grupo_Estiba', function (Blueprint $table) {
            $table->unsignedBigInteger('Id_Bodega_Nuevo')->nullable(false)->change();
            $table->foreign('Id_Bodega_Nuevo')->references('Id_Bodega_Nuevo')->on('Bodega_Nuevo');
            $table->unsignedBigInteger('Id_Punto_Dispensacion')->nullable(true)->change();
            $table->foreign('Id_Punto_Dispensacion')->references('Id_Punto_Dispensacion')->on('Punto_Dispensacion');

        });

        Schema::table('Estiba', function (Blueprint $table) {
            $table->unsignedBigInteger('Id_Grupo_Estiba')->nullable(false)->change();
            $table->foreign('Id_Grupo_Estiba')->references('Id_Grupo_Estiba')->on('Grupo_Estiba');
            $table->unsignedBigInteger('Id_Bodega_Nuevo')->nullable(false)->change();
            $table->foreign('Id_Bodega_Nuevo')->references('Id_Bodega_Nuevo')->on('Bodega_Nuevo');
            $table->unsignedBigInteger('Id_Punto_Dispensacion')->nullable(true)->change();
            $table->foreign('Id_Punto_Dispensacion')->references('Id_Punto_Dispensacion')->on('Punto_Dispensacion');
        });
    }

    public function down()
    {
        
        Schema::table('Estiba', function (Blueprint $table) {
            $table->dropForeign(['Id_Grupo_Estiba']);
            $table->dropForeign(['Id_Bodega_Nuevo']);
            $table->dropForeign(['Id_Punto_Dispensacion']);
        });

        Schema::table('Grupo_Estiba', function (Blueprint $table) {
            $table->dropForeign(['Id_Bodega_Nuevo']);
            $table->dropForeign(['Id_Punto_Dispensacion']);
        });

        Schema::table('Bodega_Nuevo', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
        });
    }
}

