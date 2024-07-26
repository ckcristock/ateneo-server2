<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class CreateBorradorContabilidadTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Borrador_Contabilidad', function (Blueprint $table) {
            $table->bigIncrements('Id_Borrador_Contabilidad');
            $table->string('Codigo', 45)->nullable();
            $table->string('Tipo_Comprobante', 45)->nullable();
            $table->integer('Identificacion_Funcionario')->nullable();
            $table->text('Datos')->nullable();
            $table->timestamp('Created_At')->nullable()->useCurrent();
            $table->timestamp('Updated_At')->useCurrentOnUpdate()->nullable()->useCurrent();
            $table->string('Estado', 45)->nullable()->default('Activa');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Borrador_Contabilidad');
    }
}
;
