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
        Schema::create('Producto_Nacionalizacion_Parcial', function (Blueprint $table) {
            $table->id('Id_Producto_Nacionalizacion_Parcial');
            $table->unsignedBigInteger('Id_Nacionalizacion_Parcial');
            $table->unsignedBigInteger('Id_Producto_Acta_Recepcion_Internacional');
            $table->integer('Cantidad');
            $table->unsignedBigInteger('Id_Producto');
            $table->decimal('Precio', 20, 8)->comment('Valor representado en dólares (USD)');
            $table->decimal('Precio_Unitario_Pesos', 20, 6);
            $table->decimal('Total_Flete', 20, 2);
            $table->decimal('Total_Seguro', 20, 2);
            $table->decimal('Total_Flete_Nacional', 20, 4);
            $table->decimal('Total_Licencia', 20, 4);
            $table->decimal('Total_Arancel', 20, 2);
            $table->decimal('Precio_Unitario_Final', 20, 6);
            $table->decimal('Total_Iva', 20, 2);
            $table->decimal('Subtotal', 20, 2)->comment('Valor representado en pesos');
            $table->decimal('Porcentaje_Flete', 20, 6)->comment('Porcentaje a aplicar, ya está dividido entre 100');
            $table->decimal('Porcentaje_Seguro', 20, 6)->comment('Porcentaje a aplicar, ya está dividido entre 100');
            $table->decimal('Porcentaje_Arancel', 20, 2)->comment('Porcentaje a aplicar, no está dividido entre 100');
            $table->decimal('Adicional_Flete_Nacional', 20, 4);
            $table->decimal('Adicional_Licencia_Importacion', 20, 4);
            $table->decimal('Adicional_Cargue', 20, 6)->nullable();
            $table->decimal('Adicional_Gasto_Bancario', 20, 6)->nullable();
            $table->double('Costo_Real', 20, 6)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Producto_Nacionalizacion_Parcial');
    }
};
