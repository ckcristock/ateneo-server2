<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActaRecepcionInternacionalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Acta_Recepcion_Internacional', function (Blueprint $table) {
            $table->bigIncrements('Id_Acta_Recepcion_Internacional');
            $table->integer('Id_Bodega')->nullable();
            $table->bigInteger('Id_Bodega_Nuevo')->nullable();
            $table->integer('Identificacion_Funcionario');
            $table->integer('Id_Orden_Compra_Internacional');
            $table->bigInteger('Id_Proveedor');
            $table->string('Codigo', 30);
            $table->string('Codigo_Qr', 150)->nullable();
            $table->timestamp('Fecha_Creacion')->useCurrent();
            $table->text('Observaciones');
            $table->double('Flete_Internacional', 20, 2)->comment("Este valor esta expresado en dolares (USD)");
            $table->double('Seguro_Internacional', 20, 2)->comment("Este valor esta expresado en dolares (USD)");
            $table->double('Flete_Nacional', 20, 2);
            $table->double('Licencia_Importacion', 20, 2);
            $table->enum('Estado', ['recibida', 'anulada'])->default('Recibida');
            $table->enum('Bloquear_Parcial', ['si', 'no'])->default('No');
            $table->bigInteger('Tercero_Flete_Internacional');
            $table->bigInteger('Tercero_Seguro_Internacional');
            $table->bigInteger('Tercero_Flete_Nacional');
            $table->bigInteger('Tercero_Licencia_Importacion');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Acta_Recepcion_Internacional');
    }
}
