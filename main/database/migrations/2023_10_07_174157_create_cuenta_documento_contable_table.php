<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class CreateCuentaDocumentoContableTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Cuenta_Documento_Contable', function (Blueprint $table) {
            $table->bigIncrements('Id_Cuenta_Documento_Contable');
            $table->integer('Id_Documento_Contable')->nullable();
            $table->integer('Id_Plan_Cuenta')->nullable();
            $table->bigInteger('Nit')->nullable();
            $table->string('Cheque', 45)->nullable();
            $table->string('Tipo_Nit', 45)->nullable();
            $table->integer('Id_Centro_Costo')->nullable();
            $table->string('Documento', 250)->nullable();
            $table->text('Concepto')->nullable();
            $table->decimal('Base', 20)->nullable();
            $table->decimal('Debito', 20)->nullable();
            $table->decimal('Credito', 20)->nullable();
            $table->decimal('Deb_Niif', 20)->nullable();
            $table->decimal('Cred_Niif', 20)->nullable();
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
        Schema::dropIfExists('Cuenta_Documento_Contable');
    }
}
;
