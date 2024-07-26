<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class CreateBalanceInicialContabilidadTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Balance_Inicial_Contabilidad', function (Blueprint $table) {
            $table->bigIncrements('Id_Balance_Inicial_Contabilidad');
            $table->date('Fecha')->nullable();
            $table->integer('Id_Plan_Cuentas')->nullable();
            $table->bigInteger('Nit')->nullable();
            $table->string('Codigo_Cuenta', 45)->nullable();
            $table->string('Codigo_Cuenta_Niif', 50)->nullable();
            $table->enum('Tipo', ['Plan Cuentas', 'Funcionario', 'Proveedor', 'Cliente'])->nullable()->default('Plan Cuentas');
            $table->decimal('Debito_PCGA', 25)->nullable();
            $table->decimal('Credito_PCGA', 25)->nullable();
            $table->decimal('Debito_NIIF', 25)->nullable();
            $table->decimal('Credito_NIIF', 25)->nullable();
            $table->integer('Id_Centro_Costo')->nullable();
            $table->string('Nombre', 500)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Balance_Inicial_Contabilidad');
    }
}
;
