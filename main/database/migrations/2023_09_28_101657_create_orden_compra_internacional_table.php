<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdenCompraInternacionalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Orden_Compra_Internacional', function (Blueprint $table) {
            $table->bigIncrements('Id_Orden_Compra_Internacional');
            $table->string('Codigo', 10)->nullable();
            $table->integer('Identificacion_Funcionario')->default(0);
            $table->timestamp('Fecha_Registro')->nullable()->useCurrent();
            $table->bigInteger('Id_Bodega')->nullable();
            $table->integer('Id_Bodega_Nuevo')->nullable();
            $table->bigInteger('Id_Proveedor')->nullable();
            $table->text('Observaciones')->nullable();
            $table->string('Puerto_Destino', 60);
            $table->double('Tasa_Dolar', 20, 2);
            $table->string('Tipo', 30)->nullable();
            $table->enum('Estado', ['pendiente', 'recibida', 'anulada', ''])->default('Pendiente');
            $table->string('Codigo_Qr', 100)->default('');
            $table->double('Flete_Internacional', 20, 2)->default(0.00)->comment("Este valor esta expresado en dolares (USD)");
            $table->double('Seguro_Internacional', 20, 2)->default(0.00)->comment("Este valor esta expresado en dolares (USD)");
            $table->double('Tramite_Sia', 20, 2)->default(0.00)->comment("Este valor esta expresado en pesos colombianos");
            $table->double('Flete_Nacional', 20, 2)->default(0.00)->comment("Este valor esta expresado en  pesos colombianos");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Orden_Compra_Internacional');
    }
}
