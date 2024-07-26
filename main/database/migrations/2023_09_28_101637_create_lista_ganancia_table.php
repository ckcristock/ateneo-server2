<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateListaGananciaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Lista_Ganancia', function (Blueprint $table) {
            $table->bigIncrements('Id_Lista_Ganancia');
            $table->string('Codigo', 100)->nullable();
            $table->string('Nombre', 100)->nullable();
            $table->integer('Porcentaje')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Lista_Ganancia');
    }
}
