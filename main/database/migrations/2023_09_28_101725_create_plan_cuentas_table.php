<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlanCuentasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Plan_Cuentas', function (Blueprint $table) {
            $table->bigIncrements('Id_Plan_Cuentas');
            $table->string('Tipo_P', 60)->nullable();
            $table->string('Tipo_Niif', 60)->nullable();
            $table->string('Codigo', 100)->nullable()->index('Codigo_idx');
            $table->string('Nombre', 100)->nullable();
            $table->string('Codigo_Niif', 100)->nullable()->index('Codigo_Niif_idx');
            $table->string('Nombre_Niif', 100)->nullable();
            $table->string('Estado', 100)->default('ACTIVO');
            $table->string('Ajuste_Contable', 100)->nullable();
            $table->string('Cierra_Terceros', 100)->nullable();
            $table->string('Movimiento', 100)->nullable();
            $table->string('Documento', 100)->nullable();
            $table->string('Base', 100)->nullable();
            $table->string('Valor', 100)->nullable();
            $table->decimal('Porcentaje', 6, 3)->nullable();
            $table->string('Centro_Costo', 100)->nullable();
            $table->string('Depreciacion', 100)->nullable();
            $table->string('Amortizacion', 100)->nullable();
            $table->string('Exogeno', 100)->nullable();
            $table->string('Naturaleza', 100)->nullable();
            $table->string('Maneja_Nit', 100)->nullable();
            $table->string('Cie_Anual', 45)->nullable();
            $table->string('Nit_Cierre', 45)->nullable();
            $table->string('Banco', 45)->nullable();
            $table->string('Cod_Banco', 45)->nullable();
            $table->string('Nit', 45)->nullable();
            $table->enum('Clase_Cta', ['corriente', 'ahorros'])->nullable();
            $table->string('Cta_Numero', 45)->nullable();
            $table->string('Reporte', 45)->nullable();
            $table->string('Niif', 45)->nullable();
            $table->decimal('Porcentaje_Real', 6, 3)->nullable();
            $table->enum('Tipo_Cierre_Mensual', ['sin asignar', 'ingresos', 'costos', 'gastos'])->default('Sin Asignar');
            $table->enum('Tipo_Cierre_Anual', ['sin asignar', 'ingresos', 'costos', 'gastos'])->default('Sin Asignar');
            $table->integer('company_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Plan_Cuentas');
    }
}
