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
        Schema::table('Inventario_Nuevo', function (Blueprint $table) {
            $table->string('Lote')->nullable();
            $table->date('Fecha_Vencimiento')->nullable();
        });
    }

    public function down()
    {
        Schema::table('Inventario_Nuevo', function (Blueprint $table) {
            $table->dropColumn('Lote');
            $table->dropColumn('Fecha_Vencimiento');
        });
    }
};
