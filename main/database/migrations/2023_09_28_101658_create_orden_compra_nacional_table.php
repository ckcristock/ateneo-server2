<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdenCompraNacionalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Orden_Compra_Nacional', function (Blueprint $table) {
            $table->bigIncrements('Id_Orden_Compra_Nacional');
            $table->string('Codigo', 10)->nullable();
            $table->integer('Identificacion_Funcionario')->nullable();
            $table->timestamp('Fecha')->nullable()->useCurrent();
            $table->bigInteger('Id_Bodega')->default(0);
            $table->bigInteger('Id_Bodega_Nuevo')->nullable();
            $table->integer('Id_Punto_Dispensacion')->default(0);
            $table->bigInteger('Id_Proveedor')->nullable();
            $table->text('Observaciones')->nullable();
            $table->date('Fecha_Entrega_Probable')->nullable();
            $table->string('Tipo', 100)->nullable();
            $table->string('Estado', 100)->default('Pendiente');
            $table->timestamp('Fecha_Creacion_Compra')->useCurrent();
            $table->string('Codigo_Qr', 100)->default('');
            $table->enum('Aprobacion', ['aprobada', 'rechazada', 'pendiente'])->default('Pendiente');
            $table->bigInteger('Id_Pre_Compra')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Orden_Compra_Nacional');
    }
}
