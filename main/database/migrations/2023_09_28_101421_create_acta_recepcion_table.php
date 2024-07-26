<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActaRecepcionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Acta_Recepcion', function (Blueprint $table) {
            $table->bigIncrements('Id_Acta_Recepcion');
            $table->bigInteger('Id_Bodega')->default(0);
            $table->bigInteger('Id_Bodega_Nuevo')->nullable();
            $table->integer('Id_Punto_Dispensacion')->default(0);
            $table->string('Identificacion_Funcionario', 100)->nullable();
            $table->string('Factura', 100)->nullable();
            $table->date('Fecha_Factura')->nullable();
            $table->text('Observaciones')->nullable();
            $table->string('Codigo', 100)->nullable();
            $table->timestamp('Fecha_Creacion')->useCurrent();
            $table->string('Codigo_Qr_Real', 100)->default('');
            $table->bigInteger('Id_Proveedor')->nullable()->index('Id_Proveedor_idx');
            $table->string('Tipo', 100)->nullable();
            $table->string('Tipo_Acta', 60)->default('Bodega');
            $table->bigInteger('Id_Orden_Compra_Nacional')->nullable();
            $table->integer('Id_Orden_Compra_Internacional')->nullable();
            $table->integer('company_id')->nullable();
            $table->string('Estado', 60)->default('Pendiente');
            $table->integer('Id_Causal_Anulacion')->nullable();
            $table->text('Observaciones_Anulacion')->nullable();
            $table->bigInteger('Funcionario_Anula')->nullable();
            $table->dateTime('Fecha_Anulacion')->nullable();
            $table->string('Codigo_Qr', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Acta_Recepcion');
    }
}
