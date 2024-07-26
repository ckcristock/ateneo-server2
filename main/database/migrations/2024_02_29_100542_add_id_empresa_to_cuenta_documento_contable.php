<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('Cuenta_Documento_Contable', function (Blueprint $table) {
        $table->unsignedBigInteger('Id_Empresa')->nullable();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down()
{
    Schema::table('Cuenta_Documento_Contable', function (Blueprint $table) {
        $table->dropColumn('Id_Empresa');
    });
}

};
