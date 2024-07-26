<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class CreateRetencionComprobanteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Retencion_Comprobante', function (Blueprint $table) {
            $table->bigIncrements('Id_Retencion_Comprobante');
            $table->integer('Id_Factura')->nullable()->default(0)->index('Id_Factura');
            $table->integer('Id_Comprobante')->index('Id_Comprobante');
            $table->integer('Id_Retencion')->index('Id_Retencion');
            $table->decimal('Valor', 20);
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
        Schema::dropIfExists('Retencion_Comprobante');
    }
}
;
