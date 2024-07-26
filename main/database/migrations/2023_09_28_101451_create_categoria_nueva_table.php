<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoriaNuevaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Categoria_Nueva', function (Blueprint $table) {
            $table->bigIncrements('Id_Categoria_Nueva');
            $table->integer('company_id')->nullable();
            $table->string('Nombre', 200)->nullable();
            $table->integer('Departamento')->nullable();
            $table->integer('Municipio')->nullable();
            $table->string('Direccion', 200)->nullable();
            $table->string('Telefono', 200)->nullable();
            $table->enum('Compra_Internacional', ['si', 'no'])->default('No');
            $table->enum('Aplica_Separacion_Categorias', ['si', 'no'])->default('No');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Categoria_Nueva');
    }
}
