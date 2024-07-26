<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('Actividad_Factura', function (Blueprint $table) {
            $table->id('Id_Actividad_Factura');
            $table->integer('Id_Funcionario');
            $table->integer('Id_Radicado');
            $table->string('Factura', 200);
            $table->datetime('Fecha_Actividad')->default(now());
            $table->text('Detalle');
            $table->string('Estado', 200);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Actividad_Factura');
    }
};
