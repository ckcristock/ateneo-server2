<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;



class CreateComprobanteConsecutivoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Comprobante_Consecutivo', function (Blueprint $table) {
            $table->bigIncrements('Id_Comprobante_Consecutivo');
            $table->string('Tipo', 45)->nullable();
            $table->string('Prefijo', 45)->nullable();
            $table->enum('Anio', ['SI', 'NO'])->nullable();
            $table->enum('Mes', ['SI', 'NO'])->nullable();
            $table->enum('Dia', ['SI', 'NO'])->nullable();
            $table->integer('Consecutivo')->nullable();
            $table->integer('company_id')->nullable();
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
        Schema::dropIfExists('Comprobante_Consecutivo');
    }
}
;
