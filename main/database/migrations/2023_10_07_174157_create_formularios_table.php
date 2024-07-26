<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class CreateFormulariosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Formularios', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('Nombre', 50)->default('');
            $table->string('Descripcion', 500)->default('');
            $table->enum('Estado', ['Activo', 'Inactivo'])->nullable()->default('Activo');
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
        Schema::dropIfExists('Formularios');
    }
}
;
