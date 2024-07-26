<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class CreateDispensacionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Dispensacion', function (Blueprint $table) {
            $table->bigIncrements('Id_Dispensacion');
            $table->string('Codigo', 200)->nullable();
            $table->string('Numero_Documento', 200)->nullable();
            $table->string('Tipo', 100)->nullable();
            $table->integer('Cuota')->nullable()->default(0);
            $table->string('EPS', 100)->nullable();
            $table->date('Fecha_Formula')->nullable();
            $table->timestamp('Fecha_Actual')->nullable()->useCurrent();
            $table->integer('Cantidad_Entregas')->nullable();
            $table->integer('Entrega_Actual')->nullable();
            $table->string('Estado', 200)->nullable();
            $table->text('Observaciones')->nullable();
            $table->integer('Productos_Entregados')->default(0);
            $table->integer('Pendientes')->nullable();
            $table->string('Tipo_Servicio', 200)->nullable();
            $table->string('CIE', 200)->nullable();
            $table->string('Doctor', 200)->nullable();
            $table->string('IPS', 200)->nullable();
            $table->bigInteger('Identificacion_Funcionario')->nullable();
            $table->integer('Id_Punto_Dispensacion')->nullable();
            $table->string('Estado_Facturacion', 100)->nullable()->default('Sin Facturar');
            $table->string('Estado_Auditoria', 100)->nullable()->default('Sin Auditar');
            $table->integer('Identificacion_Auditor')->default(0);
            $table->dateTime('Fecha_Asignado_Auditor')->nullable();
            $table->dateTime('Fecha_Auditado')->nullable();
            $table->integer('Identificacion_Facturador')->nullable()->default(0);
            $table->dateTime('Fecha_Asignado_Facturador')->nullable();
            $table->integer('Id_Factura')->nullable();
            $table->dateTime('Fecha_Facturado')->nullable();
            $table->text('Causal_No_Pago')->nullable();
            $table->integer('Facturador_Asignado')->nullable()->default(0);
            $table->string('Estado_Dispensacion', 45)->nullable();
            $table->integer('Id_Correspondencia')->nullable();
            $table->string('Estado_Correspondencia', 100)->nullable()->default('Pendiente');
            $table->string('Codigo_Qr_Real', 100)->default('');
            $table->string('Firma_Reclamante', 200)->nullable();
            $table->integer('Id_Diario_Cajas_Dispensacion')->nullable();
            $table->string('Acta_Entrega', 250)->nullable();
            $table->integer('Id_Turnero')->nullable();
            $table->string('Nombre_Prueba', 100)->nullable();
            $table->string('Codigo_Qr', 100)->nullable();
            $table->integer('Id_Servicio')->nullable();
            $table->integer('Id_Tipo_Servicio')->nullable();
            $table->string('Paciente', 500)->nullable();
            $table->integer('Id_Dispensacion_Mipres')->default(0);
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
        Schema::dropIfExists('Dispensacion');
    }
}
;
