<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('variable_products', function (Blueprint $table) {
            $table->unsignedBigInteger('Id_Producto_Acta_Recepcion')->after('valor')->nullable();
            $table->foreign('Id_Producto_Acta_Recepcion')->references('Id_Producto_Acta_Recepcion')->on('Producto_Acta_Recepcion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('variable_products', function (Blueprint $table) {
            $table->dropForeign(['Id_Producto_Acta_Recepcion']);
            $table->dropColumn('Id_Producto_Acta_Recepcion');
        });
    }
};
