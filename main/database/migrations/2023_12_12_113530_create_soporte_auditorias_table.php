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
        Schema::create('Soporte_Auditoria', function (Blueprint $table) {
            $table->id('Id_Soporte_Auditoria');
            $table->integer('Id_Tipo_Soporte')->nullable();
            $table->string('Tipo_Soporte', 1000)->nullable();
            $table->string('Cumple', 10)->nullable();
            $table->string('Archivo', 500)->nullable();
            $table->integer('Id_Auditoria')->nullable();
            $table->string('Paginas', 100)->nullable();
            $table->timestamp('current_timestamps')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Soporte_Auditoria');
    }

};
