<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductoContratoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Producto_Contrato', function (Blueprint $table) {
            $table->bigIncrements('Id_Producto_Contrato');
            $table->bigInteger('Id_Contrato')->nullable();
            $table->bigInteger('Id_Producto')->nullable();
            $table->string('Cum', 100)->nullable();
            $table->integer('Cantidad')->nullable();
            $table->double('Precio')->nullable();
            $table->double('Precio_Anterior')->nullable();
            $table->timestamp('Ultima_Actualizacion')->nullable()->useCurrent();
            $table->integer('Homologo')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Producto_Contrato');
    }
}
