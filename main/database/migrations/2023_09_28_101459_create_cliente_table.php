<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClienteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Cliente', function (Blueprint $table) {
            $table->bigIncrements('Id_Cliente');
            $table->integer('Digito_Verificacion')->nullable();
            $table->string('Tipo_Identificacion', 5)->nullable();
            $table->string('Nombre', 200)->nullable();
            $table->string('Razon_Social', 200)->nullable();
            $table->string('Primer_Nombre', 60)->nullable();
            $table->string('Segundo_Nombre', 60)->nullable();
            $table->string('Primer_Apellido', 60)->nullable();
            $table->string('Segundo_Apellido', 60)->nullable();
            $table->string('Direccion', 200)->nullable();
            $table->integer('Id_Departamento')->nullable();
            $table->integer('Id_Municipio')->nullable();
            $table->string('Ciudad', 100)->nullable();
            $table->string('Telefono_Persona_Contacto', 100)->nullable();
            $table->string('Celular', 15)->nullable();
            $table->string('Correo_Persona_Contacto', 100)->nullable();
            $table->string('Cliente_Desde', 10)->nullable();
            $table->string('Destacado', 2)->nullable();
            $table->string('Credito', 2)->nullable();
            $table->integer('Cupo')->default(0);
            $table->string('Tipo', 100)->nullable();
            $table->text('Detalles')->nullable();
            $table->string('Contacto_Compras', 100)->nullable();
            $table->string('Telefono_Contacto_Compras', 60)->nullable();
            $table->string('Email_Contacto_Compras', 60)->nullable();
            $table->string('Contacto_Pagos', 100)->nullable();
            $table->string('Telefono_Pagos', 60)->nullable();
            $table->string('Email_Pagos', 60)->nullable();
            $table->string('Regimen', 60)->nullable();
            $table->string('Animo_Lucro', 30)->nullable();
            $table->integer('Id_Codigo_Ciiu')->nullable();
            $table->string('Agente_Retencion', 30)->nullable();
            $table->string('Retencion_Factura', 45)->nullable();
            $table->string('Tipo_Reteica', 30)->nullable();
            $table->integer('Id_Plan_Cuenta_Reteica')->nullable();
            $table->integer('Id_Plan_Cuenta_Retefuente')->nullable();
            $table->string('Contribuyente', 30)->nullable();
            $table->integer('Id_Plan_Cuenta_Reteiva')->nullable();
            $table->decimal('Descuento_Pronto_Pago', 5, 2)->nullable();
            $table->integer('Descuento_Dias')->nullable();
            $table->string('Rut', 100)->nullable();
            $table->string('Estado', 100)->default('Activo');
            $table->bigInteger('Id_Zona')->nullable();
            $table->integer('Condicion_Pago')->default(30);
            $table->string('Impuesto', 100)->nullable();
            $table->string('Tipo_Valor', 100)->default('Cerrada');
            $table->bigInteger('Id_Lista_Ganancia')->nullable();
            $table->string('Latitud', 50)->nullable();
            $table->string('Longitud', 50)->nullable();
            $table->timestamp('Fecha_Registro')->nullable()->useCurrent();
            $table->enum('Autorretenedor', ['no', 'si'])->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Cliente');
    }
}
