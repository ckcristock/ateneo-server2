<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePrecioReguladoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Precio_Regulado', function (Blueprint $table) {
            $table->bigIncrements('Id_Precio_Regulado');
            $table->string('Codigo_Cum', 60)->unique('Codigo_Cum');
            $table->decimal('Precio', 50, 2);
            $table->decimal('Precio_Anterior', 50, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Precio_Regulado');
    }
}
