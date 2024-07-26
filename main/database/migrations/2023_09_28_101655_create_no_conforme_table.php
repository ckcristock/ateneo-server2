<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNoConformeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('No_Conforme', function (Blueprint $table) {
            $table->bigIncrements('Id_No_Conforme');
            $table->timestamp('Fecha_registro')->nullable()->useCurrent();
            $table->string('Codigo', 100)->nullable();
            $table->string('Persona_Reporta', 100)->nullable();
            $table->text('Descripcion')->nullable();
            $table->string('Factura', 100)->nullable();
            $table->string('Tipo', 100)->nullable();
            $table->bigInteger('Id_Remision')->nullable();
            $table->bigInteger('Id_Acta_Recepcion_Compra')->nullable();
            $table->string('Estado', 100)->default('Pendiente');
            $table->string('Codigo_Qr', 100)->default('');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('No_Conforme');
    }
}
