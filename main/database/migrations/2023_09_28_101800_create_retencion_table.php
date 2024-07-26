<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRetencionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Retencion', function (Blueprint $table) {
            $table->bigIncrements('Id_Retencion');
            $table->string('Nombre', 150)->nullable();
            $table->integer('Id_Plan_Cuenta')->nullable()->index('Id_Plan_Cuenta_idx');
            $table->double('Porcentaje')->nullable();
            $table->string('Estado', 45)->default('Activo');
            $table->longText('Descripcion')->nullable();
            $table->enum('Tipo_Retencion', ['renta', 'iva', 'ica', ''])->default('Renta');
            $table->enum('Modalidad_Retencion', ['compras', 'ventas', 'general', ''])->default('General');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Retencion');
    }
}
