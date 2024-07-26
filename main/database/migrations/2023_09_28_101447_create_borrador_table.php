<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBorradorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Borrador', function (Blueprint $table) {
            $table->bigIncrements('Id_Borrador');
            $table->string('Codigo', 100)->nullable();
            $table->string('Tipo', 100)->nullable();
            $table->longText('Texto')->nullable();
            $table->timestamp('Fecha')->useCurrent();
            $table->integer('Id_Funcionario')->nullable();
            $table->string('Nombre_Destino', 500)->nullable();
            $table->string('Estado', 60)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Borrador');
    }
}
