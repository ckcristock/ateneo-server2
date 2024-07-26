<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class CreateContabilidadComprobanteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Contabilidad_Comprobante', function (Blueprint $table) {
            $table->bigIncrements('Id_Contabilidad_Comprobante');
            $table->integer('Id_Plan_Cuentas')->nullable();
            $table->integer('Id_Comprobante')->nullable();
            $table->string('Id_Factura_Comprobante', 45)->nullable();
            $table->float('Debito', 10, 0)->nullable()->default(0);
            $table->float('Credito', 10, 0)->nullable()->default(0);
            $table->string('Codigo_Cuenta', 45)->nullable();
            $table->text('Nombre_Cuenta')->nullable();
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
        Schema::dropIfExists('Contabilidad_Comprobante');
    }
}
;
