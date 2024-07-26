<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('Actividades_Dispensacion', function (Blueprint $table) {
            $table->id('Id_Actividades_Dispensacion');
            $table->bigInteger('Id_Dispensacion')->nullable();
            $table->integer('Identificacion_Funcionario')->nullable();
            $table->timestamp('Fecha')->default(now());
            $table->text('Detalle')->nullable();
            $table->text('Estado')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('Actividades_Dispensacion');
    }
};
