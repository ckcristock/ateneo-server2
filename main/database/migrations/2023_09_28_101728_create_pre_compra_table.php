<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePreCompraTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Pre_Compra', function (Blueprint $table) {
            $table->bigIncrements('Id_Pre_Compra');
            $table->integer('Identificacion_Funcionario')->nullable();
            $table->dateTime('Fecha')->useCurrent();
            $table->integer('Id_Orden_Pedido')->nullable();
            $table->integer('Id_Proveedor')->nullable();
            $table->string('Estado', 100)->default('Pendiente');
            $table->enum('Tipo', ['medicamentos', 'materiales', 'nutriciones', 'orden_pedido'])->nullable();
            $table->enum('Tipo_Medicamento', ['todos', 'clientes', 'no pos', 'pos'])->nullable();
            $table->date('Fecha_Inicio')->nullable();
            $table->date('Fecha_Fin')->nullable();
            $table->enum('Excluir_Vencimiento', ['si', 'no'])->nullable();
            $table->integer('Meses')->nullable();
            $table->bigInteger('Id_Orden_Compra_Nacional')->nullable();
            $table->integer('Id_Contrato')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Pre_Compra');
    }
}
