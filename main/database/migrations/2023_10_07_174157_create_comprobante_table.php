<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class CreateComprobanteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Comprobante', function (Blueprint $table) {
            $table->bigIncrements('Id_Comprobante');
            $table->integer('Id_Funcionario')->default(0);
            $table->integer('Id_Cliente')->default(0);
            $table->integer('Id_Proveedor')->default(0);
            $table->date('Fecha_Comprobante');
            $table->timestamp('Fecha_Registro')->useCurrent();
            $table->string('Id_Forma_Pago', 50)->nullable();
            $table->string('Cheque', 45)->nullable();
            $table->integer('Id_Cuenta');
            $table->text('Observaciones');
            $table->text('Notas')->nullable();
            $table->string('Codigo', 60);
            $table->string('Tipo', 60);
            $table->enum('Tipo_Movimiento', ['General', 'Factura'])->default('General');
            $table->string('Codigo_Qr', 200)->nullable();
            $table->string('Estado', 60)->default('Activa');
            $table->bigInteger('Funcionario_Anula')->nullable();
            $table->dateTime('Fecha_Anulacion')->nullable();
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
        Schema::dropIfExists('Comprobante');
    }
}
;
