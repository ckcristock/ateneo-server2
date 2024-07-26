<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePuntoDispensacionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Punto_Dispensacion', function (Blueprint $table) {
            $table->bigIncrements('Id_Punto_Dispensacion');
            $table->string('Nombre', 200)->nullable();
            $table->string('Tipo', 200)->nullable();
            $table->enum('Tipo_Entrega', ['dispensacion', 'aplicacion', 'venta'])->nullable();
            $table->integer('Departamento')->nullable()->index('Departamento_idx');
            $table->integer('Municipio')->nullable();
            $table->string('Direccion', 200)->nullable();
            $table->string('Telefono', 200)->nullable();
            $table->integer('Responsable')->nullable();
            $table->string('No_Pos', 200)->nullable();
            $table->string('Turnero', 200)->nullable();
            $table->integer('Cajas')->nullable();
            $table->enum('Wacom', ['si', 'no']);
            $table->enum('Entrega_Formula', ['si', 'no'])->default('No');
            $table->enum('Entrega_Doble', ['si', 'no'])->default('No');
            $table->enum('Autorizacion', ['si', 'no'])->default('No');
            $table->enum('Tipo_Dispensacion', ['entrega', 'digitacion', 'cerrado'])->default('Entrega');
            $table->enum('Campo_Mipres', ['numero_prescripcion', 'id_direccionamiento'])->default('Numero_Prescripcion');
            $table->integer('Id_Bodega_Despacho')->nullable();
            $table->enum('Estado', ['activo', 'inactivo'])->default('Activo');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Punto_Dispensacion');
    }
}
