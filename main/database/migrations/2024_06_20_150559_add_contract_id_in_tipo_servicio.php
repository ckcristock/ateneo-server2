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
        Schema::table('Tipo_Servicio', function (Blueprint $table) {
            $table->unsignedBigInteger('contract_id')->nullable()->after('Id_Servicio');
            $table->foreign('contract_id')->references('id')->on('contracts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('Tipo_Servicio', function (Blueprint $table) {
            //
        });
    }
};
