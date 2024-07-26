<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class CreateFacturaAdministrativaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Factura_Administrativa', function (Blueprint $table) {
            $table->bigIncrements('Id_Factura_Administrativa');
            $table->enum('Activos_Fijos', ['Si', 'No'])->nullable();
            $table->integer('Id_Cliente')->nullable();
            $table->string('Tipo_Cliente', 80)->nullable();
            $table->integer('Id_Resolucion')->nullable();
            $table->timestamp('Fecha')->nullable()->useCurrent();
            $table->timestamp('Fecha_Documento')->useCurrent();
            $table->string('Codigo', 100)->nullable();
            $table->integer('Id_Centro_Costo')->nullable();
            $table->integer('Identificacion_Funcionario')->nullable();
            $table->text('Observaciones')->nullable();
            $table->string('Nota_Credito', 5)->nullable();
            $table->decimal('Valor_Nota_Credito', 10, 0)->nullable();
            $table->dateTime('Fecha_Nota')->nullable();
            $table->integer('Funcionario_Nota')->nullable();
            $table->enum('Estado_Factura', ['Sin Cancelar', 'Nota Credito', 'Pagada', 'Anulada'])->default('Sin Cancelar');
            $table->string('Codigo_Qr', 100)->nullable();
            $table->string('Cufe', 100)->nullable();
            $table->string('Procesada', 10)->default('false');
            $table->integer('Condicion_Pago')->nullable();
            $table->date('Fecha_Pago')->nullable();
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
        Schema::dropIfExists('Factura_Administrativa');
    }
}
;
