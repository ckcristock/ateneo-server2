<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Tipo_Soporte', function (Blueprint $table) {
            $table->id('Id_Tipo_Soporte');
            $table->integer('Id_Tipo_Servicio');
            $table->text('Tipo_Soporte');
            $table->text('Comentario');
            $table->enum('Pre_Auditoria', ['Si', 'No'])->default('No');
            $table->enum('Auditoria', ['Si', 'No'])->default('No');
            $table->string('Nombre_Rips', 500)->nullable();
            $table->string('Nombre_Radicacion', 100)->default('');
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
        Schema::dropIfExists('Tipo_Soporte');
    }
};
