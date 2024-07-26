<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNacionalizacionParcialTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Nacionalizacion_Parcial', function (Blueprint $table) {
            $table->bigIncrements('Id_Nacionalizacion_Parcial');
            $table->integer('Id_Acta_Recepcion_Internacional');
            $table->integer('Identificacion_Funcionario');
            $table->string('Codigo', 20)->nullable();
            $table->timestamp('Fecha_Registro')->useCurrent();
            $table->double('Tasa_Cambio', 20, 2);
            $table->enum('Estado', ['pendiente', 'nacionalizado', 'anulado', 'acomodada'])->default('Pendiente');
            $table->text('Observaciones')->nullable();
            $table->double('Tramite_Sia', 20, 2)->comment("Este valor esta representado en pesos");
            $table->double('Formulario', 20, 2)->comment("Este valor esta representado en pesos");
            $table->double('Cargue', 20, 2)->comment("Este valor esta representado en pesos");
            $table->double('Gasto_Bancario', 20, 2)->comment("Este valor esta representado en pesos");
            $table->integer('Tercero_Tramite_Sia');
            $table->integer('Tercero_Formulario');
            $table->integer('Tercero_Cargue');
            $table->integer('Tercero_Gasto_Bancario');
            $table->double('Descuento_Parcial', 20, 2)->comment("Este valor esta representado en pesos");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Nacionalizacion_Parcial');
    }
}
