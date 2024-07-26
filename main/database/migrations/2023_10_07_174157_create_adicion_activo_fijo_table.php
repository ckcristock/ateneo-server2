<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class CreateAdicionActivoFijoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Adicion_Activo_Fijo', function (Blueprint $table) {
            $table->bigIncrements('Id_Adicion_Activo_Fijo');
            $table->integer('Id_Activo_Fijo')->nullable();
            $table->integer('Id_Empresa')->nullable();
            $table->timestamp('Fecha')->nullable();
            $table->string('Tipo', 50)->nullable();
            $table->integer('Id_Tipo_Activo_Fijo')->nullable();
            $table->decimal('Costo_NIIF', 20)->nullable();
            $table->decimal('Costo_PCGA', 20)->nullable();
            $table->integer('Nit')->nullable();
            $table->decimal('Iva', 20)->nullable();
            $table->decimal('Base', 20)->nullable();
            $table->integer('Id_Centro_Costo')->nullable();
            $table->string('Nombre', 500)->nullable();
            $table->integer('Cantidad')->nullable();
            $table->string('Documento', 500)->nullable();
            $table->string('Referencia', 500)->nullable();
            $table->string('Codigo', 500)->nullable();
            $table->string('Concepto', 500)->nullable();
            $table->integer('Tipo_Depreciacion')->default(0);
            $table->decimal('Costo_Rete_Fuente', 20)->nullable();
            $table->decimal('Costo_Rete_Ica', 20)->nullable();
            $table->integer('Id_Cuenta_Rete_Ica')->nullable();
            $table->integer('Id_Cuenta_Rete_Fuente')->nullable();
            $table->bigInteger('Identificacion_Funcionario')->nullable();
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
        Schema::dropIfExists('Adicion_Activo_Fijo');
    }
}
;
