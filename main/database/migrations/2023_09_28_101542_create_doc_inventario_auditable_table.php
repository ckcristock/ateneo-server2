<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocInventarioAuditableTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Doc_Inventario_Auditable', function (Blueprint $table) {
            $table->bigIncrements('Id_Doc_Inventario_Auditable');
            $table->integer('Id_Bodega')->nullable();
            $table->timestamp('Fecha_Inicio')->nullable();
            $table->dateTime('Fecha_Fin')->nullable();
            $table->integer('Funcionario_Digita')->nullable();
            $table->integer('Funcionario_Cuenta')->nullable();
            $table->integer('Funcionario_Autorizo')->nullable();
            $table->longText('Productos_Correctos')->nullable();
            $table->longText('Productos_Diferencia')->nullable();
            $table->text('Observaciones')->nullable();
            $table->string('Estado', 50)->default('Abierto');
            $table->integer('Id_Inventario_Auditable_Nuevo')->nullable();
            $table->longText('Lista_Productos')->nullable();
            $table->integer('Funcionario_Anula')->nullable();
            $table->dateTime('Fecha_Anulacion')->nullable();
            $table->text('Observaciones_Anulacion')->nullable();
            $table->dateTime('Fecha')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Doc_Inventario_Auditable');
    }
}
