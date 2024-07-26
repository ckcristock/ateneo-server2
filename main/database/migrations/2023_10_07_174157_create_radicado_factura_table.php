<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class CreateRadicadoFacturaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Radicado_Factura', function (Blueprint $table) {
            $table->bigIncrements('Id_Radicado_Factura');
            $table->integer('Id_Radicado');
            $table->integer('Id_Factura');
            $table->enum('Estado_Factura_Radicacion', ['Radicada', 'Pagada', 'Glosada'])->default('Radicada');
            $table->text('Observacion')->nullable();
            $table->float('Total_Glosado', 20)->default(0);
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
        Schema::dropIfExists('Radicado_Factura');
    }
}
;
