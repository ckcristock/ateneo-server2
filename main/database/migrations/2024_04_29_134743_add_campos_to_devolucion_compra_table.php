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
        Schema::table('Devolucion_Compra', function (Blueprint $table) {
            $table->string('Soporte', 250)->nullable()->after('Estado');
            $table->text('acta')->nullable()->after('Empresa_Envio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('Devolucion_Compra', function (Blueprint $table) {
            $table->dropColumn('Soporte');
            $table->dropColumn('acta');
        });
    }
};
