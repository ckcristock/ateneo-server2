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
            $table->dropColumn('Cantidad_Leo');
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
            $table->integer('Cantidad_Leo')->default(0);
        });
    }
};
