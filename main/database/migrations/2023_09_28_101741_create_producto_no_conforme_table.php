<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductoNoConformeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Producto_No_Conforme', function (Blueprint $table) {
            $table->bigIncrements('Id_Producto_No_Conforme');
            $table->bigInteger('Id_Producto')->nullable();
            $table->bigInteger('Id_No_Conforme')->nullable();
            $table->bigInteger('Id_Compra')->nullable();
            $table->string('Tipo_Compra', 100);
            $table->bigInteger('Id_Acta_Recepcion')->nullable();
            $table->integer('Cantidad');
            $table->bigInteger('Id_Causal_No_Conforme')->nullable();
            $table->text('Observaciones');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Producto_No_Conforme');
    }
}
