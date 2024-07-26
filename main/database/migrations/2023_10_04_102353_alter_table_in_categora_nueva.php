<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableInCategoraNueva extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Categoria_Nueva', function (Blueprint $table) {
            $table->tinyInteger('Activo')->default(1);
            $table->tinyInteger('Fijo')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Categoria_Nueva', function (Blueprint $table) {
            //
        });
    }
}
