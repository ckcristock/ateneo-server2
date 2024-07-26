<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class CreateMovimientoContableTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Movimiento_Contable', function (Blueprint $table) {
            $table->bigIncrements('Id_Movimiento_Contable');
            $table->string('Id_Plan_Cuenta', 50)->default('');
            $table->dateTime('Fecha_Movimiento')->useCurrent();
            $table->integer('Id_Modulo')->comment('Id del tipo de registro que se va a guardar ej. Factura, Comprobante, etc');
            $table->integer('Id_Registro_Modulo')->nullable()->comment('Id del registro perteneciente al tipo que se guardo ej. Id_Modulo = 1(Factura Venta), Id_Registro_Modulo = 1523.');
            $table->decimal('Debe', 20);
            $table->decimal('Haber', 20);
            $table->decimal('Debe_Niif', 20)->nullable()->default(0);
            $table->decimal('Haber_Niif', 20)->nullable()->default(0);
            $table->bigInteger('Nit')->comment('Numero de documento de la persona asociada al movimiento');
            $table->enum('Tipo_Nit', ['Cliente', 'Funcionario', 'Proveedor', 'Eps', 'Arl', 'ICBF', 'Sena', 'Caja_Compensacion', 'Fondo_Pension', 'Empresa', 'Migracion_Mantis'])->default('Cliente');
            $table->enum('Estado', ['Activo', 'Anulado'])->default('Activo');
            $table->text('Documento');
            $table->text('Detalles')->nullable();
            $table->timestamp('Fecha_Registro')->nullable()->useCurrent();
            $table->bigInteger('Id_Centro_Costo')->nullable();
            $table->enum('Mantis', ['Si', 'No'])->default('No');
            $table->text('Numero_Comprobante')->nullable();
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
        Schema::dropIfExists('Movimiento_Contable');
    }
}
;
