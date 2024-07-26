<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdenPedidoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Orden_Pedido', function (Blueprint $table) {
            $table->bigIncrements('Id_Orden_Pedido');
            $table->integer('Id_Agentes_Cliente')->default(0);
            $table->string('Orden_Compra_Cliente')->default('0');
            $table->string('Archivo_Compra_Cliente')->default('0');
            $table->date('Fecha_Probable_Entrega')->nullable();
            $table->integer('Identificacion_Funcionario')->default(0);
            $table->string('Observaciones', 2000)->nullable();
            $table->timestamp('Fecha')->nullable()->useCurrent();
            $table->integer('Id_Cliente')->nullable();
            $table->string('Estado', 50)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Orden_Pedido');
    }
}
