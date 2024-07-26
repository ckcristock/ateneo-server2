<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDevolucionCompraTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Devolucion_Compra', function (Blueprint $table) {
            $table->bigIncrements('Id_Devolucion_Compra');
            $table->bigInteger('Id_No_Conforme')->nullable();
            $table->bigInteger('Identificacion_Funcionario')->nullable();
            $table->text('Observaciones')->nullable();
            $table->timestamp('Fecha')->useCurrent();
            $table->string('Codigo', 100);
            $table->string('Codigo_Qr', 100)->nullable();
            $table->bigInteger('Id_Proveedor')->nullable();
            $table->integer('Id_Bodega')->nullable();
            $table->integer('Id_Bodega_Nuevo')->nullable();
            $table->enum('Estado', ['activa', 'anulada', 'enviada', 'aprobada', 'alistada'])->default('Activa');
            $table->integer('Estado_Alistamiento')->nullable();
            $table->bigInteger('Fase_1')->nullable();
            $table->dateTime('Inicio_Fase1')->nullable();
            $table->dateTime('Fin_Fase1')->nullable();
            $table->integer('Fase_2')->nullable();
            $table->dateTime('Inicio_Fase2')->nullable();
            $table->string('Guia', 100)->nullable();
            $table->string('Empresa_Envio', 500)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Devolucion_Compra');
    }
}
