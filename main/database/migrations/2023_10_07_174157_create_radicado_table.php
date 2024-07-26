<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class CreateRadicadoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Radicado', function (Blueprint $table) {
            $table->bigIncrements('Id_Radicado');
            $table->string('Consecutivo', 20)->nullable();
            $table->string('Numero_Radicado', 100)->nullable();
            $table->integer('Id_Funcionario');
            $table->date('Fecha_Radicado')->nullable();
            $table->date('Fecha_Cierre')->nullable();
            $table->integer('Id_Cliente');
            $table->integer('Id_Departamento')->nullable()->default(0);
            $table->integer('Id_Regimen');
            $table->string('Tipo_Servicio', 500)->nullable();
            $table->integer('Id_Tipo_Servicio')->nullable();
            $table->string('Codigo', 10);
            $table->text('Observacion');
            $table->dateTime('Fecha_Registro')->useCurrent();
            $table->enum('Estado', ['PreRadicada', 'Radicada', 'Cerrada', 'Anulada'])->default('PreRadicada');
            $table->enum('Sigespro', ['Si', 'No'])->nullable()->default('Si');
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
        Schema::dropIfExists('Radicado');
    }
}
;
