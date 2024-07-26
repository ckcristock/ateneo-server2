<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('Dispensacion_Reclamante', function (Blueprint $table) {
            $table->id('Id');
            $table->string('Reclamante_Id', 50)->default('');
            $table->string('Dispensacion_Id', 50)->default('');
            $table->string('Parentesco', 255);
        });
    }

    public function down()
    {
        Schema::dropIfExists('Dispensacion_Reclamante');
    }
};
