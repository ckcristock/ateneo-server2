<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class CreatePacienteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Paciente', function (Blueprint $table) {
            $table->bigIncrements('Id_Paciente');
            $table->string('Tipo_Documento', 100)->nullable();
            $table->integer('Id_Departamento')->nullable();
            $table->integer('Cod_Departamento')->nullable();
            $table->string('Codigo_Municipio', 11)->nullable();
            $table->integer('Cod_Municipio_Dane')->nullable();
            $table->integer('Cod_Municipio_Dian')->nullable();
            $table->string('Primer_Apellido', 200)->nullable();
            $table->string('Segundo_Apellido', 200)->nullable();
            $table->string('Primer_Nombre', 200)->nullable();
            $table->string('Segundo_Nombre', 200)->nullable();
            $table->date('Fecha_Nacimiento')->nullable();
            $table->string('Genero', 200)->nullable();
            $table->integer('Id_Nivel')->nullable();
            $table->integer('Id_Regimen')->nullable();
            $table->string('Direccion', 200)->nullable();
            $table->string('Telefono', 200)->nullable();
            $table->string('Correo', 200)->nullable();
            $table->string('EPS', 200)->nullable();
            $table->string('Nit', 100)->nullable();
            $table->string('Nit_IPS', 100)->nullable();
            $table->string('IPS', 150)->nullable();
            $table->enum('Estado', ['Activo', 'Inactivo'])->default('Activo');
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
        Schema::dropIfExists('Paciente');
    }
}
;
