<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCentroCostoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Centro_Costo', function (Blueprint $table) {
            $table->bigIncrements('Id_Centro_Costo');
            $table->string('Nombre')->nullable();
            $table->string('Codigo')->nullable();
            $table->integer('Id_Centro_Padre')->nullable();
            $table->integer('Id_Tipo_Centro')->nullable();
            $table->integer('Valor_Tipo_Centro')->nullable();
            $table->string('Estado', 50)->default('Activo');
            $table->enum('Movimiento', ['si', 'no'])->default('No');
            $table->integer('company_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Centro_Costo');
    }
}
