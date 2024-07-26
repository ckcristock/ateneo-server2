<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveColumnsFromProductoDocInventarioFisicoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Producto_Doc_Inventario_Fisico', function (Blueprint $table) {
            $table->dropColumn(['Lote', 'Fecha_Vencimiento']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Producto_Doc_Inventario_Fisico', function (Blueprint $table) {
            $table->string('Lote', 100)->nullable();
            $table->date('Fecha_Vencimiento')->nullable();
        });
    }
}
