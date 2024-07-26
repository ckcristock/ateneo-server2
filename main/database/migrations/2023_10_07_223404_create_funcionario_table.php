<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class CreateFuncionarioTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Funcionario', function (Blueprint $table) {
            $table->bigIncrements('Id_Funcionario');
            $table->string('Codigo', 200)->nullable();
            $table->enum('Suspendido', ['NO', 'SI'])->nullable()->default('NO');
            $table->enum('Liquidado', ['NO', 'SI'])->nullable()->default('NO');
            $table->string('Nombres', 45)->nullable();
            $table->string('Apellidos', 45)->nullable();
            $table->string('Primer_Nombre', 150)->nullable();
            $table->string('Segundo_Nombre', 150)->nullable();
            $table->string('Primer_Apellido', 150)->nullable();
            $table->string('Segundo_Apellido', 150)->nullable();
            $table->bigInteger('Id_Grupo')->nullable();
            $table->bigInteger('Id_Dependencia')->nullable();
            $table->bigInteger('Id_Cargo')->nullable();
            $table->date('Fecha_Nacimiento')->nullable();
            $table->string('Lugar_Nacimiento', 500)->nullable();
            $table->string('Tipo_Sangre', 3)->nullable();
            $table->string('Telefono', 15)->nullable();
            $table->string('Celular', 15)->nullable();
            $table->string('Correo', 45)->nullable();
            $table->string('Direccion_Residencia', 200)->nullable();
            $table->string('Estado_Civil', 15)->nullable();
            $table->string('Grado_Instruccion', 200)->nullable();
            $table->string('Titulo_Estudio', 200)->nullable();
            $table->string('Talla_Pantalon', 3)->nullable();
            $table->string('Talla_Bata', 3)->nullable();
            $table->string('Talla_Botas', 3)->nullable();
            $table->string('Talla_Camisa', 4)->nullable();
            $table->string('Username', 45)->nullable();
            $table->string('Password', 45)->nullable();
            $table->string('Imagen', 45)->nullable();
            $table->string('Autorizado', 2)->nullable()->default('No');
            $table->integer('Salario')->nullable();
            $table->integer('Eps')->nullable();
            $table->integer('Pension')->nullable();
            $table->integer('Caja_Compensacion')->nullable();
            $table->string('Bonos', 45)->nullable();
            $table->string('Fecha_Ingreso', 10)->nullable();
            $table->integer('Hijos')->nullable()->default(0);
            $table->dateTime('Ultima_Sesion')->nullable();
            $table->timestamp('Fecha_Registrado')->useCurrentOnUpdate()->nullable()->useCurrent();
            $table->string('personId', 100)->nullable();
            $table->string('persistedFaceId', 200)->nullable();
            $table->enum('Tipo_Turno', ['Rotativo', 'Fijo', 'Mixto', 'Libre'])->nullable()->default('Fijo');
            $table->bigInteger('Id_Turno')->nullable()->default(0);
            $table->integer('Id_Proceso')->nullable()->default(0);
            $table->integer('Lider_Grupo')->nullable()->default(0);
            $table->date('Fecha_Retiro')->nullable()->default('2100-12-31');
            $table->string('Sexo', 50)->nullable();
            $table->integer('Jefe')->nullable()->default(0);
            $table->enum('Salarios', ['Si', 'No'])->nullable()->default('No');
            $table->enum('Permiso_App', ['Si', 'No'])->nullable()->default('Si');
            $table->string('Gcm_Id', 200)->nullable();
            $table->enum('Tipo', ['Propio', 'Externo'])->default('Propio');
            $table->integer('Auxilio_No_Salarial')->nullable();
            $table->string('Actualizar', 100)->nullable();
            $table->integer('Id_Fondo_Pension')->nullable();
            $table->integer('Id_Caja_Compensacion')->nullable();
            $table->integer('Id_Eps')->nullable();
            $table->string('Id_Tipo_Contrato', 50)->nullable();
            $table->date('Fecha_Inicio_Contrato')->nullable();
            $table->date('Fecha_Fin_Contrato')->nullable();
            $table->integer('Id_Salario')->nullable();
            $table->integer('Id_Riesgo')->nullable();
            $table->string('Firma', 500)->nullable();
            $table->integer('Id_Banco')->nullable();
            $table->text('Cuenta')->nullable();
            $table->decimal('Vacaciones_Acumuladas', 20, 4)->default(0);
            $table->enum('Ver_Costo', ['Si', 'No'])->default('No');
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
        Schema::dropIfExists('Funcionario');
    }
}
;
