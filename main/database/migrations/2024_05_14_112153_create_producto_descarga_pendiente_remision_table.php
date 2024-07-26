<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('Producto_Descarga_Pendiente_Remision', function (Blueprint $table) {
            $table->integer('Id_Producto_Descarga_Pendiente_Remision')->autoIncrement();
            $table->bigInteger('Id_Remision')->nullable();
            $table->bigInteger('Id_Dispensacion')->nullable();
            $table->string('Id_Paciente', 20)->nullable();
            $table->integer('Id_Producto')->nullable();
            $table->integer('Cantidad')->nullable();
            $table->string('Lote', 50);
            $table->integer('Identificacion_Funcionario')->nullable();
            $table->timestamp('Fecha')->useCurrent();
            $table->bigInteger('Id_Producto_Remision')->nullable();
            $table->integer('Id_Descarga_Pendiente_Remision')->nullable();
            $table->enum('Entregado', ['Si', 'No'])->default('No');

            // Índices
            $table->index('Id_Remision');
            $table->index('Id_Remision', 'idx_id_remision');
            $table->index('Id_Dispensacion', 'idx_id_dispensacion');
            $table->index('Id_Paciente', 'idx_id_paciente');
            $table->index('Id_Producto', 'idx_id_producto');
            $table->index('Identificacion_Funcionario', 'idx_ident_funcionario');
            $table->index('Id_Producto_Remision', 'idx_id_producto_remision');
            $table->index('Id_Descarga_Pendiente_Remision', 'idx_id_desc_pend_remision');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Producto_Descarga_Pendiente_Remision');
    }
};
