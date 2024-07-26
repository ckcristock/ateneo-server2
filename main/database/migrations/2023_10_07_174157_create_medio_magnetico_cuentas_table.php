<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class CreateMedioMagneticoCuentasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Medio_Magnetico_Cuentas', function (Blueprint $table) {
            $table->bigIncrements('Id_Medio_Magnetico_Cuentas');
            $table->integer('Id_Medio_Magnetico')->nullable();
            $table->integer('Id_Plan_Cuenta')->nullable();
            $table->string('Concepto', 45)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Medio_Magnetico_Cuentas');
    }
}
;
