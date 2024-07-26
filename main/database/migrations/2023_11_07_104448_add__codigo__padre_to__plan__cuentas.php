<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCodigoPadreToPlanCuentas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Plan_Cuentas', function (Blueprint $table) {
            $table->integer('Codigo_Padre')->nullable(); // Define las propiedades del nuevo campo
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Plan_Cuentas', function (Blueprint $table) {
            //
        });
    }
}
