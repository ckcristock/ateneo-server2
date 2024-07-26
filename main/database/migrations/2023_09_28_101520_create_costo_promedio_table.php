<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCostoPromedioTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Costo_Promedio', function (Blueprint $table) {
            $table->bigIncrements('Id_Costo_Promedio');
            $table->bigInteger('Id_Producto')->nullable();
            $table->decimal('Costo_Promedio', 50, 2)->nullable();
            $table->dateTime('Ultima_Actualizacion')->nullable();
            $table->decimal('Costo_Anterior', 50, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Costo_Promedio');
    }
}
