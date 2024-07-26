<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class CreateFacturaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Factura', function (Blueprint $table) {
            $table->bigIncrements('Id_Factura');
            $table->timestamp('Fecha_Documento')->nullable()->useCurrent();
            $table->bigInteger('Id_Bodega')->nullable();
            $table->bigInteger('Id_Cliente')->nullable();
            $table->bigInteger('Id_Funcionario')->nullable();
            $table->bigInteger('Id_Lista_Ganancia')->nullable();
            $table->text('Observacion_Factura')->nullable();
            $table->string('Codigo', 100)->nullable();
            $table->string('Estado_Factura', 200)->nullable();
            $table->bigInteger('Id_Dispensacion')->nullable();
            $table->integer('Condicion_Pago');
            $table->integer('Cuota')->nullable();
            $table->date('Fecha_Pago');
            $table->string('Tipo', 200);
            $table->integer('Id_Factura_Asociada')->nullable();
            $table->string('Codigo_Qr', 100)->default('');
            $table->integer('Id_Causal_Anulacion')->nullable();
            $table->integer('Funcionario_Anula')->nullable();
            $table->enum('Estado_Radicacion', ['Pendiente', 'Radicada', 'Anulada'])->default('Pendiente');
            $table->integer('Id_Resolucion')->nullable();
            $table->integer('Actualizado')->default(0);
            $table->string('Cufe', 100)->nullable();
            $table->string('ZipKey', 100)->nullable();
            $table->longText('ZipBase64Bytes')->nullable();
            $table->string('Procesada', 100)->nullable();
            $table->string('Nota_Credito', 2)->nullable();
            $table->integer('Valor_Nota_Credito')->nullable();
            $table->dateTime('Fecha_Anulacion')->nullable();
            $table->integer('Funcionario_Nota')->nullable();
            $table->dateTime('Fecha_Nota')->nullable();
            $table->integer('Id_Dispensacion2')->nullable();
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
        Schema::dropIfExists('Factura');
    }
}
;
