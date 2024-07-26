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
        Schema::table('Factura_Venta', function (Blueprint $table) {
            $table->date('Fecha_Acuse_Factura')->nullable();
            $table->date('Fecha_Acuse_Mercancia')->nullable();
            $table->string('Estado_Aceptacion', 100)->nullable();
            $table->date('Fecha_Estado')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('Factura_Venta', function (Blueprint $table) {
            $table->dropColumn('Fecha_Acuse_Factura');
            $table->dropColumn('Fecha_Acuse_Mercancia');
            $table->dropColumn('Estado_Aceptacion');
            $table->dropColumn('Fecha_Estado');
        });
    }
};


