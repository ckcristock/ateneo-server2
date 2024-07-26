<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class CreateFacturaComprobanteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Factura_Comprobante', function (Blueprint $table) {
            $table->bigIncrements('Id_Factura_Comprobante');
            $table->integer('Id_Comprobante')->index('Id_Comprobante');
            $table->string('Factura', 60);
            $table->decimal('Excenta', 20);
            $table->decimal('Gravada', 20);
            $table->decimal('Iva', 20)->nullable();
            $table->decimal('Total', 20);
            $table->decimal('Neto_Factura', 20);
            $table->decimal('Valor', 20);
            $table->integer('Id_Factura');
            $table->integer('Id_Cuenta_Descuento')->nullable()->default(0);
            $table->decimal('ValorDescuento', 20)->nullable()->default(0);
            $table->decimal('ValorMayorPagar', 20)->nullable()->default(0);
            $table->integer('Id_Cuenta_MayorPagar')->nullable()->default(0);
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
        Schema::dropIfExists('Factura_Comprobante');
    }
}
;
