<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProveedorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Proveedor', function (Blueprint $table) {
            $table->bigIncrements('Id_Proveedor');
            $table->string('Tipo_Identificacion', 5)->nullable();
            $table->integer('Digito_Verificacion')->nullable();
            $table->string('Primer_Nombre', 100)->nullable();
            $table->string('Segundo_Nombre', 100)->nullable();
            $table->string('Primer_Apellido', 100)->nullable();
            $table->string('Segundo_Apellido', 100)->nullable();
            $table->string('Nombre', 200)->nullable();
            $table->string('Razon_Social', 200)->nullable();
            $table->string('Ciudad', 100)->nullable();
            $table->string('Direccion', 200)->nullable();
            $table->string('Telefono', 100)->nullable();
            $table->string('Celular', 15)->nullable();
            $table->string('Correo', 100)->nullable();
            $table->string('Descripcion', 500)->nullable();
            $table->integer('Meses_Devolucion')->nullable();
            $table->integer('Id_Departamento')->nullable();
            $table->integer('Id_Municipio')->nullable();
            $table->string('Asesor_Comercial', 100)->nullable();
            $table->string('Telefono_Asesor', 60)->nullable();
            $table->string('Email_Asesor', 100)->nullable();
            $table->string('Tipo_Retencion', 45)->nullable();
            $table->string('Tipo_Reteica', 45)->nullable();
            $table->integer('Id_Plan_Cuenta_Reteica')->nullable();
            $table->string('Animo_Lucro', 45)->nullable();
            $table->string('Ley_1429_2010', 45)->nullable();
            $table->string('Id_Codigo_Ciiu', 11)->nullable();
            $table->integer('Id_Plan_Cuenta_Retefuente')->nullable();
            $table->integer('Id_Plan_Cuenta_Reteiva')->nullable();
            $table->string('Contribuyente', 45)->nullable();
            $table->text('Detalle')->nullable();
            $table->string('Confiable', 100)->nullable();
            $table->string('Regimen', 100)->nullable();
            $table->string('Tipo', 100)->nullable();
            $table->integer('Condicion_Pago')->nullable();
            $table->integer('Dias_Descuento')->nullable();
            $table->decimal('Porcentaje_Descuento', 10, 2)->nullable();
            $table->string('Estado', 100)->default('Activo');
            $table->string('Rut', 250)->nullable();
            $table->string('Pais', 45)->nullable();
            $table->string('Pais_Dian', 20)->nullable();
            $table->string('Tipo_Tercero', 45)->default('Proveedor');
            $table->timestamp('Fecha_Registro')->nullable()->useCurrent();
            $table->integer('Identificacion_Funcionario')->nullable();
            $table->bigInteger('Cupo')->default(1000000000);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Proveedor');
    }
}
