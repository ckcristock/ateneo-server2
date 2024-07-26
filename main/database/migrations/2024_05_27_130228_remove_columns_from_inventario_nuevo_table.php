<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveColumnsFromInventarioNuevoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Inventario_Nuevo', function (Blueprint $table) {
            $table->dropColumn(['Codigo_CUM', 'Lote', 'Fecha_Vencimiento']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Inventario_Nuevo', function (Blueprint $table) {
            $table->string('Codigo_CUM')->nullable();
            $table->string('Lote')->nullable();
            $table->date('Fecha_Vencimiento')->nullable();
        });
    }
}
