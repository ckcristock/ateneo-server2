<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Producto', function (Blueprint $table) {
            $table->bigIncrements('Id_Producto');
            $table->mediumText('Nombre_Comercial')->nullable(); //nombre
            $table->mediumText('Nombre_General')->nullable(); //nombre
            $table->integer('Cantidad_Minima')->nullable(); //va
            $table->integer('Cantidad_Maxima')->nullable();//va
            $table->integer('Unidad_Medida')->nullable();//va
            $table->string('Codigo_Barras', 200)->nullable()->index('Codigo_Barras_idx');//va
            $table->mediumText('Imagen')->nullable();//va
            $table->integer('Id_Categoria')->nullable();//va
            $table->string('Referencia', 100)->nullable();//va
            $table->enum('Gravado', ['si', 'no'])->default('No'); // agregar valor del impuesto //va
            $table->enum('Actualizado', ['si', 'no'])->default('No');
            $table->string('Unidad_Empaque', 500)->nullable()->comment("Cantidad de unidades por caja");//va
            $table->enum('Estado', ['activo', 'inactivo'])->default('Activo');
            $table->integer('Id_Subcategoria')->nullable();
            $table->integer('company_id')->nullable();
            $table->unsignedBigInteger('impuesto_id')->nullable();
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
        Schema::dropIfExists('Producto');
    }
}
