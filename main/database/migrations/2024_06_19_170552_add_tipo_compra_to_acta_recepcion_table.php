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
        Schema::table('Acta_Recepcion', function (Blueprint $table) {
            $table->enum('tipo_compra', ['compras', 'solicitud'])->default('compras');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Acta_Recepcion', function (Blueprint $table) {
            $table->dropColumn('tipo_compra');
        });
    }
};
