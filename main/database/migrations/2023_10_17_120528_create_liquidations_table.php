<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLiquidationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('liquidations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('person_id');
            $table->string('motivo', 100)->nullable();
            $table->enum('justa_causa', ['si', 'no'])->nullable();
            $table->date('fecha_contratacion');
            $table->date('fecha_terminacion')->nullable();
            $table->bigInteger('dias_liquidados')->nullable();
            $table->bigInteger('dias_vacaciones')->nullable();
            $table->bigInteger('salario_base')->nullable();
            $table->bigInteger('vacaciones_base')->nullable();
            $table->bigInteger('cesantias_base')->nullable();
            $table->string('dominicales_incluidas', 50)->nullable();
            $table->bigInteger('cesantias_anterior')->nullable();
            $table->bigInteger('intereses_cesantias')->nullable();
            $table->bigInteger('otros_ingresos')->nullable();
            $table->bigInteger('prestamos')->nullable();
            $table->bigInteger('otras_deducciones')->nullable();
            $table->string('notas', 500)->nullable();
            $table->bigInteger('valor_dias_vacaciones')->nullable();
            $table->bigInteger('valor_cesantias')->nullable();
            $table->bigInteger('valor_prima')->nullable();
            $table->bigInteger('sueldo_pendiente')->nullable();
            $table->bigInteger('auxilio_pendiente')->nullable();
            $table->bigInteger('otros')->nullable();
            $table->bigInteger('salud')->nullable();
            $table->bigInteger('pension')->nullable();
            $table->bigInteger('total')->nullable();
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
        Schema::dropIfExists('liquidations');
    }
}
