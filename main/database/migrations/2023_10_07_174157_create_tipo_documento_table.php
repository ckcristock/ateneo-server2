<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class CreateTipoDocumentoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Tipo_Documento', function (Blueprint $table) {
            $table->bigIncrements('Id_Tipo_Documento');
            $table->string('Codigo', 5)->nullable();
            $table->integer('Cod_Dian')->nullable();
            $table->string('Nombre', 100)->nullable();
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
        Schema::dropIfExists('Tipo_Documento');
    }
}
;
