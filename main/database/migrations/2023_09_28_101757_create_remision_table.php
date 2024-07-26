<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRemisionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Remision', function (Blueprint $table) {
            $table->bigIncrements('Id_Remision');
            $table->bigInteger('Id_Contrato')->nullable();
            $table->timestamp('Fecha')->useCurrent();
            $table->string('Tipo', 200)->nullable();
            $table->integer('Identificacion_Funcionario')->nullable();
            $table->text('Observaciones')->nullable();
            $table->string('Codigo', 100)->nullable();
            $table->string('Tipo_Origen', 100)->nullable();
            $table->integer('Id_Origen')->nullable();
            $table->integer('Id_Orden_Pedido')->nullable();
            $table->string('Nombre_Origen', 100)->nullable();
            $table->string('Tipo_Destino', 100)->nullable();
            $table->integer('Id_Destino')->nullable();
            $table->string('Nombre_Destino', 1000)->nullable();
            $table->string('Tipo_Lista', 200)->nullable();
            $table->integer('Id_Lista')->default(1);
            $table->string('Estado', 100)->nullable();
            $table->integer('Estado_Alistamiento')->nullable();
            $table->string('Prioridad', 100)->nullable();
            $table->integer('Id_Factura')->nullable();
            $table->integer('Peso_Remision')->default(0);
            $table->string('Codigo_Qr', 100)->default('');
            $table->decimal('Costo_Remision', 12, 2)->nullable();
            $table->string('Tipo_Bodega', 100)->default('MEDICAMENTOS');
            $table->string('Fecha_Anulacion', 100)->nullable();
            $table->integer('Funcionario_Anula')->default(0);
            $table->integer('Fase_1')->default(0);
            $table->integer('Fase_2')->default(0);
            $table->string('Guia', 100)->nullable();
            $table->string('Empresa_Envio', 500)->nullable();
            $table->decimal('Subtotal_Remision', 12, 2)->default(0.00);
            $table->decimal('Descuento_Remision', 12, 2)->default(0.00);
            $table->decimal('Impuesto_Remision', 12, 2)->default(0.00);
            $table->string('Orden_Compra', 100)->nullable();
            $table->dateTime('Inicio_Fase1')->nullable();
            $table->dateTime('Fin_Fase1')->nullable();
            $table->dateTime('Inicio_Fase2')->nullable();
            $table->dateTime('Fin_Fase2')->nullable();
            $table->enum('Entrega_Pendientes', ['si', 'no'])->default('No');
            $table->string('Observacion_Anulacion', 500)->nullable();
            $table->integer('Id_Categoria')->nullable();
            $table->date('FIni_Rotativo')->nullable();
            $table->date('FFin_Rotativo')->nullable();
            $table->integer('Eps_Rotaivo')->nullable();
            $table->integer('Id_Subcategoria')->nullable();
            $table->integer('Id_Categoria_Nueva')->nullable();
            $table->integer('Id_Grupo_Estiba')->nullable();
            $table->integer('Meses')->nullable();
            $table->bigInteger('Id_Factura_Venta')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Remision');
    }
}
