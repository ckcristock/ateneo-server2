<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class CreateCuentaContableComprobanteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Cuenta_Contable_Comprobante', function (Blueprint $table) {
            $table->bigIncrements('Id_Cuenta_Contable_Comprobante');
            $table->integer('Id_Plan_Cuenta')->index('Id_Plan_Cuenta');
            $table->float('Valor', 10, 0);
            $table->integer('Impuesto');
            $table->integer('Cantidad');
            $table->text('Observaciones');
            $table->float('Subtotal', 10, 0);
            $table->integer('Id_Comprobante')->index('Id_Comprobante');
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
        Schema::dropIfExists('Cuenta_Contable_Comprobante');
    }
}
;
