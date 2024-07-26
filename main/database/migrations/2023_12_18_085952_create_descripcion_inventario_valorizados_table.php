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
        Schema::create('Descripcion_Inventario_Valorizado', function (Blueprint $table) {
            $table->id('Id_Descripcion_Inventario_Valorizado');
            $table->unsignedBigInteger('Id_Inventario_Valorizado')->nullable();
            $table->unsignedBigInteger('Id_Origen')->nullable();
            $table->string('Tipo_Origen', 60)->nullable();
            $table->decimal('Costo_Promedio', 50, 2)->nullable();
            $table->unsignedBigInteger('Id_Producto')->nullable();
            $table->integer('Cantidad');
            $table->unsignedBigInteger('Id_Inventario_Nuevo')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Descripcion_Inventario_Valorizado');
    }
};
