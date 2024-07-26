<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class CreateFacturaCapitaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Factura_Capita', function (Blueprint $table) {
            $table->bigIncrements('Id_Factura_Capita');
            $table->bigInteger('Id_Cliente')->nullable();
            $table->integer('Identificacion_Funcionario')->nullable();
            $table->timestamp('Fecha_Documento')->useCurrent();
            $table->string('Mes', 100)->nullable();
            $table->longText('Observacion')->nullable();
            $table->string('Codigo', 100)->nullable();
            $table->bigInteger('Id_Departamento')->nullable();
            $table->integer('Id_Punto_Dispensacion')->nullable()->default(0);
            $table->integer('Id_Regimen')->nullable();
            $table->float('Cuota_Moderadora', 10, 0)->nullable();
            $table->string('Estado_Factura', 60)->nullable()->default('Sin Cancelar');
            $table->string('Codigo_Qr', 100)->default('');
            $table->enum('Estado_Radicacion', ['Pendiente', 'Radicada'])->default('Pendiente');
            $table->integer('Id_Resolucion')->nullable();
            $table->string('Cufe', 100)->nullable();
            $table->string('ZipKey', 100)->nullable();
            $table->longText('ZipBase64Bytes')->nullable();
            $table->string('Procesada', 100)->nullable();
            $table->string('Nota_Credito', 10)->nullable();
            $table->integer('Valor_Nota_Credito')->nullable();
            $table->integer('Funcionario_Nota')->nullable();
            $table->dateTime('Fecha_Nota')->nullable();
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
        Schema::dropIfExists('Factura_Capita');
    }
}
;
