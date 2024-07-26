<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('Producto_Dispensacion_Mipres', function (Blueprint $table) {
            $table->id('Id_Producto_Dispensacion_Mipres');
            $table->integer('Id_Producto')->nullable();
            $table->string('Codigo_Cum')->nullable();
            $table->string('NoPrescripcion')->nullable();
            $table->string('ID')->nullable();
            $table->string('IDDireccionamiento')->nullable();
            $table->bigInteger('Id_Dispensacion_Mipres')->nullable();
            $table->integer('Cantidad')->nullable();
            $table->integer('CantidadMipres')->nullable();
            $table->string('CodSerTecAEntregar')->nullable();
            $table->string('IdProgramacion')->default('0');
            $table->string('IdEntrega')->default('0');
            $table->string('IdReporteEntrega')->nullable();
            $table->bigInteger('IdFactura')->nullable();
            $table->string('Tipo_Tecnologia')->nullable();
            $table->datetime('Fecha_Programacion')->nullable();
            $table->datetime('Fecha_Entrega')->nullable();
            $table->datetime('Fecha_Reporte_Entrega')->nullable();
            $table->datetime('Fecha_Factura')->nullable();
            $table->decimal('Valor_Reportado', 50, 2)->nullable();
            $table->string('Actualizado')->nullable();
            $table->string('Anulado')->nullable();
            $table->string('Estado_Direccionamiento')->nullable();
            $table->string('Observacion_Reportes')->nullable();
            $table->string('Anulado2')->nullable();
            $table->string('Actualizado2')->nullable();
            $table->string('Tipo')->nullable();
            $table->string('Anulado3')->nullable();
            $table->string('Actualizado3')->nullable();
            $table->string('Cum_Reportado')->nullable();
            $table->datetime('Fecha_Entrega_Reportada')->nullable();
            $table->integer('ConTec')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Producto_Dispensacion_Mipres');
    }
};
