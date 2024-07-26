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
        Schema::create('Producto_Ajuste_Individual', function (Blueprint $table) {
            $table->id('Id_Producto_Ajuste_Individual');
            $table->unsignedBigInteger('Id_Ajuste_Individual')->nullable();
            $table->unsignedBigInteger('Id_Producto')->nullable();
            $table->unsignedBigInteger('Id_Inventario')->nullable();
            $table->unsignedBigInteger('Id_Inventario_Nuevo')->nullable();
            $table->string('Lote', 100)->nullable();
            $table->string('Lote_Nuevo', 100)->nullable();
            $table->date('Fecha_Vencimiento')->nullable();
            $table->string('Fecha_Vencimiento_Nueva', 100)->nullable();
            $table->integer('Cantidad')->nullable();
            $table->float('Costo')->nullable();
            $table->text('Observaciones')->nullable();
            $table->unsignedBigInteger('Id_Nueva_Estiba')->nullable()->comment('cambio estiba - estiba seleccionada');
            $table->unsignedBigInteger('Id_Estiba_Acomodada')->nullable()->comment('estiba en la que se acomodó');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Producto_Ajuste_Individual');
    }
};
