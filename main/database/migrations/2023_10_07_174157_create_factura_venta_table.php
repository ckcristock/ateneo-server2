<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class CreateFacturaVentaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Factura_Venta', function (Blueprint $table) {
            $table->bigIncrements('Id_Factura_Venta');
            $table->timestamp('Fecha_Documento')->nullable()->useCurrent();
            $table->bigInteger('Id_Cliente')->nullable();
            $table->bigInteger('Id_Funcionario')->nullable();
            $table->bigInteger('Id_Lista_Ganancia')->nullable();
            $table->text('Observacion_Factura_Venta')->nullable();
            $table->string('Codigo', 100)->nullable();
            $table->string('Estado', 200)->nullable()->default('Pendiente');
            $table->bigInteger('Id_Cotizacion_Venta')->nullable();
            $table->integer('Condicion_Pago');
            $table->date('Fecha_Pago');
            $table->string('Codigo_Qr_Real', 100)->default('');
            $table->text('Remisiones')->nullable();
            $table->integer('Funcionario_Anula')->nullable();
            $table->dateTime('Fecha_Anulacion')->nullable();
            $table->integer('Id_Causal_Anulacion')->nullable();
            $table->string('Factura_Ventacol', 45)->nullable();
            $table->string('Codigo_Qr', 45)->nullable();
            $table->integer('Id_Resolucion')->nullable();
            $table->string('Cufe', 100)->nullable();
            $table->string('ZipKey', 100)->nullable();
            $table->longText('ZipBase64Bytes')->nullable();
            $table->string('Procesada', 100)->nullable();
            $table->string('Nota_Credito', 2)->nullable();
            $table->integer('Valor_Nota_Credito')->nullable();
            $table->integer('Funcionario_Nota')->nullable();
            $table->dateTime('Fecha_Nota')->nullable();
            $table->bigInteger('Id_Cliente2')->nullable();
            $table->string('Observacion_Factura_Venta2')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Factura_Venta');
    }
}
;
