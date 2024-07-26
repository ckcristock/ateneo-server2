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
        Schema::table('Producto_Acta_Recepcion', function (Blueprint $table) {
            $table->unsignedBigInteger('Id_Causal_No_Conforme')->after('Cumple')->nullable();
            $table->foreign('Id_Causal_No_Conforme')->references('Id_Causal_No_Conforme')->on('Causal_No_Conforme');
            $table->integer('nonconforming_quantity')->after('Id_Causal_No_Conforme')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('Producto_Acta_Recepcion', function (Blueprint $table) {
            $table->dropColumn('Id_Causal_No_Conforme');
            $table->dropColumn('nonconforming_quantity');
        });
    }
};
