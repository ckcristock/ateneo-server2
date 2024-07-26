<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivoFijoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Activo_Fijo', function (Blueprint $table) {
            $table->bigIncrements('Id_Activo_Fijo');
            $table->string('Tipo', 50)->nullable();
            $table->integer('Id_Tipo_Activo_Fijo');
            $table->decimal('Costo_NIIF', 20)->nullable();
            $table->decimal('Costo_PCGA', 20)->nullable();
            $table->decimal('Ultima_Adicion', 10)->nullable();
            $table->timestamp('Fecha_Ultima_Adicion')->nullable();
            $table->bigInteger('Nit')->nullable();
            $table->decimal('Iva', 20)->nullable();
            $table->decimal('Base', 20)->nullable();
            $table->decimal('Iva_Niif', 20)->nullable()->default(0);
            $table->decimal('Base_Niif', 20)->nullable()->default(0);
            $table->integer('Id_Centro_Costo')->nullable();
            $table->string('Nombre', 500)->nullable();
            $table->integer('Cantidad')->nullable();
            $table->string('Documento', 500)->nullable();
            $table->string('Referencia', 500)->nullable();
            $table->string('Codigo', 500)->nullable();
            $table->string('Codigo_Activo_Fijo', 60)->nullable();
            $table->string('Concepto', 500)->nullable();
            $table->integer('Tipo_Depreciacion')->default(0)->comment('0 corresponde a deprecioacion normal y 1 a depreciacion en un mes ');
            $table->decimal('Costo_Rete_Fuente', 20)->nullable();
            $table->decimal('Costo_Rete_Ica', 20)->nullable();
            $table->decimal('Costo_Rete_Fuente_NIIF', 20)->nullable();
            $table->decimal('Costo_Rete_Ica_NIIF', 20)->nullable();
            $table->integer('Id_Cuenta_Rete_Ica')->nullable();
            $table->integer('Id_Cuenta_Rete_Fuente')->nullable();
            $table->bigInteger('Identificacion_Funcionario')->nullable();
            $table->dateTime('Fecha')->useCurrent();
            $table->string('Estado', 45)->nullable()->default('Activo');
            $table->bigInteger('Funcionario_Anula')->nullable();
            $table->dateTime('Fecha_Anulacion')->nullable();
            $table->integer('Id_Descripcion_Factura_Administrativa')->nullable();
            $table->integer('Id_Empresa')->default(1);
            $table->integer('company_id')->default(1);
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
        Schema::dropIfExists('activo_fijo');
    }
}
