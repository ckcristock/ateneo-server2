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
        Schema::create('Facturas_Cliente_Mantis', function (Blueprint $table) {
            $table->id('Id_Facturas_Cliente_Mantis');
            $table->unsignedBigInteger('Nit_Cliente')->nullable()->index('Nit_Cliente_idx');
            $table->string('Factura', 45)->nullable()->index('Factura_idx');
            $table->date('Fecha_Factura')->nullable();
            $table->decimal('Saldo', 20, 2)->nullable();
            $table->string('Estado', 45)->default('Pendiente');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Facturas_Cliente_Mantis');
    }
};
