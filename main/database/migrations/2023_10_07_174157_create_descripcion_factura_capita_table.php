<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class CreateDescripcionFacturaCapitaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Descripcion_Factura_Capita', function (Blueprint $table) {
            $table->bigIncrements('Id_Descripcion_Factura_Capita');
            $table->integer('Id_Factura_Capita')->nullable();
            $table->longText('Descripcion')->nullable();
            $table->integer('Cantidad')->nullable();
            $table->decimal('Precio', 15)->nullable();
            $table->integer('Descuento')->nullable();
            $table->integer('Impuesto')->nullable();
            $table->decimal('Total', 15)->nullable();
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
        Schema::dropIfExists('Descripcion_Factura_Capita');
    }
}
;
