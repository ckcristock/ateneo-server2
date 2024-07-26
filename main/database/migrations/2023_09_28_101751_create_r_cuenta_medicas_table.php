<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRCuentaMedicasTable extends Migration
{
    /*! REVISAR BASE DE DATOS EN CPANEL */
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('r_cuenta_medicas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('certificado_bancario')->nullable();
            $table->integer('company_id')->nullable();
            $table->string('documento')->nullable();
            $table->string('factura')->nullable();
            $table->bigInteger('third_part_id')->nullable();
            $table->string('rut')->nullable();
            $table->string('seguridad_social')->nullable();
            $table->integer('type')->nullable();
            $table->timestamps();
            $table->string('numero_factura', 50)->nullable();
            $table->string('numero_pacientes', 50)->nullable();
            $table->string('valor_factura', 50)->nullable();
            $table->string('valor_glosado', 50)->default('0');
            $table->string('observaciones', 50)->nullable();
            $table->string('camara_comercio')->nullable();
            $table->string('status', 50)->default('Pendiente');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('r_cuenta_medicas');
    }
}
